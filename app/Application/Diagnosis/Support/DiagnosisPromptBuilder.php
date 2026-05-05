<?php

namespace App\Application\Diagnosis\Support;

class DiagnosisPromptBuilder
{
    public function build(
        string $description,
        DiagnosisKnowledgeContext $knowledgeContext,
        PatientProfile $profile,
        bool $hasImage
    ): string {
        $sourcesText = $this->buildSourcesText($knowledgeContext->sources);
        $owidText = $this->buildOwidText($knowledgeContext->owidInsights);
        $profileText = $this->buildProfileText($profile);

        $domainText = $knowledgeContext->domain !== '' ? $knowledgeContext->domain : 'neutral';
        $severityText = $knowledgeContext->triageSignals->severity !== '' ? $knowledgeContext->triageSignals->severity : 'средняя';
        $redFlagsText = $knowledgeContext->triageSignals->redFlags !== []
            ? implode('; ', $knowledgeContext->triageSignals->redFlags)
            : 'не обнаружены';

        $sourcesContext = $sourcesText !== ''
            ? "Ниже внешние источники по симптомам, используй их для рассуждения:\n{$sourcesText}"
            : "Внешних релевантных источников для этого запроса не найдено. Не придумывай ссылки и не заполняй sources.\n";

        return "Ты медицинский ассистент для предварительного triage.\n".
            "Пользователь дал описание симптомов: {$description}\n".
            "Определенный домен симптомов: {$domainText}.\n".
            "Rule-based оценка тяжести: {$severityText}. Rule-based red flags: {$redFlagsText}.\n".
            $profileText.
            ($hasImage
                ? "Пользователь также приложил изображение. Учти визуальные признаки при формировании ответа.\n"
                : '').
            $sourcesContext.
            "Ниже контекстные рекомендации OWID:\n{$owidText}\n".
            "Тон ответа: сдержанный, но человеческий. Пиши коротко, ясно и без нагнетания.\n".
            "Формат: короткие смысловые карточки, чтобы пользователь быстро понял, что делать.\n".
            "Не смешивай разные медицинские домены. Если домен respiratory, не выводи кардиологический диагноз без явных red flags (например, давящая боль в груди, выраженная одышка в покое, иррадиация в руку/челюсть).\n".
            "Считай список источников уже отфильтрованным и ранжированным внутри домена, опирайся прежде всего на них.\n".
            "Отвечай СТРОГО на русском языке.\n".
            "Не придумывай ссылки. Если источников недостаточно, оставь sources пустым массивом.\n".
            "Верни ТОЛЬКО JSON без markdown с полями:\n".
            "diagnosis (строка), confidence (низкая|средняя|высокая), urgency (низкая|средняя|высокая|срочно), ".
            "severity (легкая|средняя|тяжелая|критическая), about (строка), confidence_reason (строка), possible_causes (массив строк), care_plan (массив строк), do_not_do (массив строк), ".
            "home_care_window (строка), red_flags (массив строк), followup_questions (массив из 2-3 строк), sources (массив объектов с полями title,url).";
    }

    private function buildSourcesText(array $sources): string
    {
        $sourcesText = '';
        foreach ($sources as $index => $source) {
            $title = $source['title'] ?? 'Без названия';
            $url = $source['url'] ?? '';
            $snippet = $source['snippet'] ?? '';
            $sourcesText .= ($index + 1).". {$title}\nURL: {$url}\nФрагмент: {$snippet}\n\n";
        }

        return $sourcesText;
    }

    private function buildOwidText(array $owidInsights): string
    {
        $owidText = '';
        foreach ($owidInsights as $index => $item) {
            $title = $item['title'] ?? 'OWID метрика';
            $advice = $item['advice'] ?? 'Следите за факторами риска.';
            $why = $item['why'] ?? 'Фактор связан с осложнениями здоровья.';
            $today = $item['today'] ?? 'Запланируйте профилактический осмотр.';
            $url = $item['url'] ?? '';
            $owidText .= ($index + 1).". {$title}\nСовет: {$advice}\nПочему важно: {$why}\nЧто сделать сегодня: {$today}\nURL: {$url}\n";
        }

        return $owidText;
    }

    private function buildProfileText(PatientProfile $profile): string
    {
        if (! $profile->hasData()) {
            return '';
        }

        $genderRu = match ($profile->gender) {
            'male' => 'мужской',
            'female' => 'женский',
            'other' => 'другой',
            default => 'не указан',
        };
        $ageText = $profile->age !== null ? (string) $profile->age : 'не указан';

        return "Профиль пациента: возраст {$ageText}, пол {$genderRu}. Учитывай это при оценке.\n";
    }
}
