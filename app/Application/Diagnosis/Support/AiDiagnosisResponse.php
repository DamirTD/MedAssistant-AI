<?php

namespace App\Application\Diagnosis\Support;

use Illuminate\Http\Client\Response;

class AiDiagnosisResponse
{
    public function __construct(
        public readonly Response $response,
        public readonly bool $usedVision,
        public readonly bool $deepSeekAttempted,
        public readonly ?string $imageNote,
        public readonly string $aiProvider,
        public readonly string $aiModel
    ) {
    }
}
