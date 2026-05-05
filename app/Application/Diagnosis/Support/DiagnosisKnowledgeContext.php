<?php

namespace App\Application\Diagnosis\Support;

readonly class DiagnosisKnowledgeContext
{
    /**
     * @param array<int, array<string, mixed>> $sources
     * @param array<int, array<string, mixed>> $owidInsights
     */
    public function __construct(
        public string $domain,
        public TriageSignals $triageSignals,
        public array $sources,
        public array $owidInsights
    ) {
    }
}
