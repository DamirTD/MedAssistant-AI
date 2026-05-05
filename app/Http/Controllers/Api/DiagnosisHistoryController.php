<?php

namespace App\Http\Controllers\Api;

use App\Contracts\Services\DiagnosisHistoryServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\DiagnosisHistory\DiagnosisHistoryIndexRequest;
use App\Http\Requests\Api\DiagnosisHistory\DiagnosisHistoryShowRequest;

class DiagnosisHistoryController extends Controller
{
    public function __construct(
        private readonly DiagnosisHistoryServiceInterface $diagnosisHistoryService
    ) {
    }

    public function index(DiagnosisHistoryIndexRequest $request)
    {
        $validated = $request->validated();
        $perPage = (int) ($validated['per_page'] ?? 10);

        return response()->json(
            $this->diagnosisHistoryService->getUserHistories($request->user(), $perPage)
        );
    }

    public function show(DiagnosisHistoryShowRequest $request, int $id)
    {
        return response()->json(
            $this->diagnosisHistoryService->getUserHistoryById($request->user(), $id)
        );
    }
}
