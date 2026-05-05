<?php

namespace App\Application\Diagnosis\Queries;

use Illuminate\Http\UploadedFile;

readonly class AnalyzeDiagnosisQuery
{
    public function __construct(
        public string $description,
        public ?UploadedFile $image,
        public ?int $age,
        public ?string $gender
    ) {
    }
}

