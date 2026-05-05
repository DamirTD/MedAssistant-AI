<?php

namespace App\Handlers\Auth;

use App\Models\User;

class AuthHandler
{
    public function createUser(array $payload): User
    {
        return User::create($payload);
    }

    public function findUserByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function updateUser(User $user, array $payload): User
    {
        $user->fill($payload);
        $user->save();

        return $user->refresh();
    }
}
