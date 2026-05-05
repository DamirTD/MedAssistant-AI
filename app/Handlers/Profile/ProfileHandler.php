<?php

namespace App\Handlers\Profile;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ProfileHandler
{
    public function getLastHistory(User $user): ?Model
    {
        return $user->diagnosisHistories()->latest()->first();
    }

    public function getAnalysesCount(User $user): int
    {
        return $user->diagnosisHistories()->count();
    }

    public function saveUser(User $user): void
    {
        $user->save();
    }
}
