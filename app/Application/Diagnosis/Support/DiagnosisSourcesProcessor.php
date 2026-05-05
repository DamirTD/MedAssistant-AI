<?php

namespace App\Application\Diagnosis\Support;

class DiagnosisSourcesProcessor
{
    public function rerankWithinDomain(array $sources, string $description, ?string $domain): array
    {
        if ($sources === []) {
            return [];
        }

        $descriptionLc = mb_strtolower($description);
        $keywords = $this->getDomainKeywords((string) ($domain ?? ''));
        $englishTokens = $this->buildEnglishTokensFromDescription($description);
        $ranked = [];

        foreach ($sources as $source) {
            $text = mb_strtolower(
                ((string) ($source['title'] ?? '')).' '.
                ((string) ($source['snippet'] ?? '')).' '.
                ((string) ($source['url'] ?? '')).' '.
                ((string) ($source['source_domain'] ?? ''))
            );
            $score = 0;

            foreach ($keywords as $keyword) {
                if ($keyword !== '' && str_contains($text, $keyword)) {
                    $score += 3;
                }
            }

            foreach (preg_split('/\s+/u', $descriptionLc) ?: [] as $token) {
                $token = trim($token);
                if (mb_strlen($token) < 4) {
                    continue;
                }
                if (str_contains($text, $token)) {
                    $score++;
                }
            }

            foreach ($englishTokens as $token) {
                if ($token !== '' && str_contains($text, $token)) {
                    $score += 2;
                }
            }

            $host = (string) parse_url((string) ($source['url'] ?? ''), PHP_URL_HOST);
            $hostLc = mb_strtolower($host);
            if ($hostLc !== '' && (str_contains($hostLc, 'nlm.nih.gov') || str_contains($hostLc, 'medlineplus.gov'))) {
                $score += 2;
            }

            $source['_score'] = $score;
            $ranked[] = $source;
        }

        usort($ranked, static fn (array $a, array $b): int => (($b['_score'] ?? 0) <=> ($a['_score'] ?? 0)));

        return array_map(static function (array $item): array {
            unset($item['_score']);

            return $item;
        }, array_values(array_slice($ranked, 0, 6)));
    }

    public function filterValidSources(array $sources): array
    {
        $filtered = [];
        foreach ($sources as $source) {
            $title = trim((string) ($source['title'] ?? ''));
            $url = trim((string) ($source['url'] ?? ''));
            if ($title === '' || ! preg_match('/^https?:\/\//i', $url)) {
                continue;
            }
            $filtered[] = [
                'title' => $title,
                'url' => $url,
                'snippet' => (string) ($source['snippet'] ?? ''),
                'source_domain' => (string) ($source['source_domain'] ?? ''),
                'language' => (string) ($source['language'] ?? ''),
            ];
        }

        return array_values(array_slice($filtered, 0, 6));
    }

    public function filterValidInsights(array $items): array
    {
        $result = [];
        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }
            $url = trim((string) ($item['url'] ?? ''));
            if ($url !== '' && ! preg_match('/^https?:\/\//i', $url)) {
                continue;
            }
            $result[] = $item;
        }

        return array_values(array_slice($result, 0, 3));
    }

    private function getDomainKeywords(string $domain): array
    {
        $domains = config('medical_triage.symptom_domains', []);
        $config = is_array($domains[$domain] ?? null) ? $domains[$domain] : [];
        $keywords = is_array($config['positive_keywords'] ?? null) ? $config['positive_keywords'] : [];

        return array_map(
            static fn (mixed $keyword): string => mb_strtolower((string) $keyword),
            $keywords
        );
    }

    private function buildEnglishTokensFromDescription(string $description): array
    {
        $text = mb_strtolower($description);
        $dictionary = config('medical_sources.translation.ru_to_en', []);
        $tokens = [];

        if (! is_array($dictionary)) {
            return [];
        }

        foreach ($dictionary as $ruStem => $enTerm) {
            $ruStem = mb_strtolower(trim((string) $ruStem));
            $enTerm = mb_strtolower(trim((string) $enTerm));
            if ($ruStem === '' || $enTerm === '') {
                continue;
            }
            if (! str_contains($text, $ruStem)) {
                continue;
            }

            foreach (preg_split('/\s+/u', $enTerm) ?: [] as $part) {
                $part = trim($part);
                if (mb_strlen($part) >= 3) {
                    $tokens[] = $part;
                }
            }
        }

        return array_values(array_unique($tokens));
    }
}
