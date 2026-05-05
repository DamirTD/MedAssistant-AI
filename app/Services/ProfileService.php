<?php

namespace App\Services;

use App\Contracts\Services\ProfileServiceInterface;
use App\Handlers\Profile\ProfileHandler;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ProfileService implements ProfileServiceInterface
{
    public function __construct(
        private readonly ProfileHandler $handler
    ) {
    }

    public function getProfileData(User $user): array
    {
        $lastHistory = $this->handler->getLastHistory($user);

        return [
            'user' => $this->serializeUser($user),
            'stats' => [
                'analyses_count'   => $this->handler->getAnalysesCount($user),
                'last_analysis_at' => $lastHistory?->created_at,
                'last_diagnosis'   => $lastHistory?->response_payload['diagnosis'] ?? null,
            ],
        ];
    }

    public function updateEmail(User $user, string $currentPassword, string $newEmail): array
    {
        $this->ensureCurrentPasswordIsValid($currentPassword, (string) $user->password);
        $user->email = $newEmail;
        $this->handler->saveUser($user);

        return [
            'message' => 'Email успешно обновлен.',
            'user'    => $this->serializeUser($user),
        ];
    }

    public function updatePassword(User $user, string $currentPassword, string $newPassword): array
    {
        $this->ensureCurrentPasswordIsValid($currentPassword, (string) $user->password);
        $user->password = Hash::make($newPassword);
        $this->handler->saveUser($user);

        return [
            'message' => 'Пароль успешно обновлен.',
        ];
    }

    private function ensureCurrentPasswordIsValid(string $currentPassword, string $hashedPassword): void
    {
        if (! Hash::check($currentPassword, $hashedPassword)) {
            throw ValidationException::withMessages([
                'current_password' => ['Текущий пароль указан неверно.'],
            ]);
        }
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
