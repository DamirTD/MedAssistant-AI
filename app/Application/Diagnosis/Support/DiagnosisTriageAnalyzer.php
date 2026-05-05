<?php

namespace App\Application\Diagnosis\Support;

class DiagnosisTriageAnalyzer
{
    public function detectDomain(string $description): string
    {
        $domains = config('medical_triage.symptom_domains', []);
        if (! is_array($domains) || $domains === []) {
            return 'neutral';
        }

        $text = mb_strtolower($description);
        $bestDomain = 'neutral';
        $bestScore = 0;

        foreach ($domains as $domain => $config) {
            if (! is_array($config)) {
                continue;
            }

            $score = 0;
            $positive = is_array($config['positive_keywords'] ?? null) ? $config['positive_keywords'] : [];
            $negative = is_array($config['negative_keywords'] ?? null) ? $config['negative_keywords'] : [];

            foreach ($positive as $keyword) {
                $keywordLc = mb_strtolower((string) $keyword);
                if ($keywordLc !== '' && str_contains($text, $keywordLc)) {
                    $score += 2;
                }
            }

            foreach ($negative as $keyword) {
                $keywordLc = mb_strtolower((string) $keyword);
                if ($keywordLc !== '' && str_contains($text, $keywordLc)) {
                    $score -= 1;
                }
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestDomain = (string) $domain;
            }
        }

        return $bestScore > 0 ? $bestDomain : 'neutral';
    }

    public function evaluateSignals(string $description): TriageSignals
    {
        $text = mb_strtolower($description);
        $rules = config('medical_triage.triage_rules', []);

        $redFlags = [];
        $redFlagRules = is_array($rules['red_flags'] ?? null) ? $rules['red_flags'] : [];
        foreach ($redFlagRules as $rule) {
            $needle = mb_strtolower(trim((string) ($rule['needle'] ?? '')));
            $label = trim((string) ($rule['label'] ?? ''));
            if ($needle === '' || $label === '') {
                continue;
            }
            if (str_contains($text, $needle)) {
                $redFlags[] = $label;
            }
        }

        $severity = 'легкая';
        $severityKeywords = is_array($rules['severity_keywords'] ?? null) ? $rules['severity_keywords'] : [];
        foreach (['критическая', 'тяжелая', 'средняя'] as $level) {
            $keywords = is_array($severityKeywords[$level] ?? null) ? $severityKeywords[$level] : [];
            foreach ($keywords as $keyword) {
                $keyword = mb_strtolower(trim((string) $keyword));
                if ($keyword !== '' && str_contains($text, $keyword)) {
                    $severity = $level;
                    break 2;
                }
            }
        }

        if (count($redFlags) >= 2) {
            $severity = 'критическая';
        } elseif (count($redFlags) === 1 && $severity === 'легкая') {
            $severity = 'тяжелая';
        }

        return new TriageSignals(
            severity: $severity,
            redFlags: array_values(array_unique($redFlags))
        );
    }
}
