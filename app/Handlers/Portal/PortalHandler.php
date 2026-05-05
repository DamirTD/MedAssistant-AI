<?php

namespace App\Handlers\Portal;

use App\Models\User;
use Illuminate\Support\Collection;

class PortalHandler
{
    public function getAllAnalyses(User $user): Collection
    {
        return $user->diagnosisHistories()
            ->latest()
            ->get(['created_at', 'response_payload']);
    }

    public function getRecentAnalyses(User $user, int $limit = 5): Collection
    {
        return $user->diagnosisHistories()
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function getAnalysesCount(User $user): int
    {
        return $user->diagnosisHistories()->count();
    }
}
