<?php

namespace App\Handlers\Diagnosis;

use App\Models\DiagnosisHistory;
use App\Models\User;

class DiagnosisHandler
{
    public function storeHistory(User $user, array $payload): DiagnosisHistory
    {
        return DiagnosisHistory::create([
            'user_id' => $user->id,
            ...$payload,
        ]);
    }
}
