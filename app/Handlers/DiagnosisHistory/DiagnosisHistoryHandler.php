<?php

namespace App\Handlers\DiagnosisHistory;

use App\Models\DiagnosisHistory;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DiagnosisHistoryHandler
{
    public function paginateByUser(User $user, int $perPage): LengthAwarePaginator
    {
        return $user->diagnosisHistories()
            ->latest()
            ->paginate($perPage);
    }

    public function findByUserAndId(User $user, int $id): DiagnosisHistory
    {
        return $user->diagnosisHistories()
            ->whereKey($id)
            ->firstOrFail();
    }
}
