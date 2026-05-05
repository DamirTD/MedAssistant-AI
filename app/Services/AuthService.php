<?php

namespace App\Services;

use App\Contracts\Services\AuthServiceInterface;
use App\Handlers\Auth\AuthHandler;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService implements AuthServiceInterface
{
    public function __construct(
        private readonly AuthHandler $handler
    ) {
    }

    public function register(array $payload): array
    {
        $user  = $this->handler->createUser($payload);
        $token = $user->createToken('api')->plainTextToken;

        return [
            'token' => $token,
            'user'  => $this->serializeUser($user),
        ];
    }

    public function login(array $payload): array
    {
        $user = $this->handler->findUserByEmail((string) $payload['email']);

        if (! $user || ! Hash::check((string) $payload['password'], (string) $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Неверный email или пароль.'],
            ]);
        }

        $token = $user->createToken('api')->plainTextToken;

        return [
            'token' => $token,
            'user'  => $this->serializeUser($user),
        ];
    }

    public function me(User $user): array
    {
        return [
            'user' => $this->serializeUser($user),
        ];
    }

    public function logout(User $user): array
    {
        $user->currentAccessToken()?->delete();

        return [
            'message' => 'Вы успешно вышли из аккаунта.',
        ];
    }

    private function serializeUser(User $user): array
    {
        return [
            'id'         => $user->id,
            'name'       => $user->name,
            'email'      => $user->email,
            'created_at' => $user->created_at,
        ];
    }
}
