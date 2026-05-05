<?php

namespace App\Contracts\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface DiagnosisHistoryServiceInterface
{
    public function getUserHistories(User $user, int $perPage): LengthAwarePaginator;

    public function getUserHistoryById(User $user, int $id): array;
}
