<?php

namespace App\Contracts\Services;

use App\Models\User;

interface AuthServiceInterface
{
    public function register(array $payload): array;

    public function login(array $payload): array;

    public function me(User $user): array;

    public function logout(User $user): array;
}
