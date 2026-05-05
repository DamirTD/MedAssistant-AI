<?php

namespace App\Contracts\Services;

use App\Models\User;

interface ProfileServiceInterface
{
    public function getProfileData(User $user): array;

    public function updateEmail(User $user, string $currentPassword, string $newEmail): array;

    public function updatePassword(User $user, string $currentPassword, string $newPassword): array;
}
