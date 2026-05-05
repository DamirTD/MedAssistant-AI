<?php

namespace App\Http\Controllers\Api;

use App\Contracts\Services\ProfileServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Profile\ProfileShowRequest;
use App\Http\Requests\Api\Profile\UpdateEmailRequest;
use App\Http\Requests\Api\Profile\UpdatePasswordRequest;

class ProfileController extends Controller
{
    public function __construct(
        private readonly ProfileServiceInterface $profileService
    ) {
    }

    public function show(ProfileShowRequest $request)
    {
        return response()->json($this->profileService->getProfileData($request->user()));
    }

    public function updateEmail(UpdateEmailRequest $request)
    {
        $validated = $request->validated();

        return response()->json(
            $this->profileService->updateEmail(
                user: $request->user(),
                currentPassword: (string) $validated['current_password'],
                newEmail: (string) $validated['new_email']
            )
        );
    }

    public function updatePassword(UpdatePasswordRequest $request)
    {
        $validated = $request->validated();

        return response()->json(
            $this->profileService->updatePassword(
                user: $request->user(),
                currentPassword: (string) $validated['current_password'],
                newPassword: (string) $validated['new_password']
            )
        );
    }
}
