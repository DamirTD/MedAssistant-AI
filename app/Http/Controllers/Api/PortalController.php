<?php

namespace App\Http\Controllers\Api;

use App\Contracts\Services\PortalServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Portal\PortalShowRequest;

class PortalController extends Controller
{
    public function __construct(
        private readonly PortalServiceInterface $portalService
    ) {
    }

    public function show(PortalShowRequest $request)
    {
        return response()->json($this->portalService->getPortalData($request->user()));
    }
}
