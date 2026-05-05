<?php

namespace App\Application\Diagnosis\Support;

readonly class PatientProfile
{
    public function __construct(
        public ?int $age,
        public ?string $gender
    ) {
    }

    public function hasData(): bool
    {
        return $this->age !== null || $this->gender !== null;
    }
}
