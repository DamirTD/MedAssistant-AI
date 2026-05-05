<?php

namespace App\Contracts\Services;

use App\Models\User;

interface PortalServiceInterface
{
    public function getPortalData(User $user): array;
}
