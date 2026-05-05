<?php

namespace App\Services;

use App\Contracts\Services\DiagnosisHistoryServiceInterface;
use App\Handlers\DiagnosisHistory\DiagnosisHistoryHandler;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DiagnosisHistoryService implements DiagnosisHistoryServiceInterface
{
    public function __construct(
        private readonly DiagnosisHistoryHandler $handler
    ) {
    }

    public function getUserHistories(User $user, int $perPage): LengthAwarePaginator
    {
        return $this->handler->paginateByUser($user, $perPage);
    }

    public function getUserHistoryById(User $user, int $id): array
    {
        return [
            'item' => $this->handler->findByUserAndId($user, $id),
        ];
    }
}
