<?php

namespace App\Contracts\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;

interface DiagnosisServiceInterface
{
    public function analyze(?User $user, string $description, ?UploadedFile $image, ?int $age, ?string $gender): array;
}
