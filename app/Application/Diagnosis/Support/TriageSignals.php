<?php

namespace App\Application\Diagnosis\Support;

readonly class TriageSignals
{
    /**
     * @param array<int, string> $redFlags
     */
    public function __construct(
        public string $severity,
        public array $redFlags
    ) {
    }
}
