<?php

namespace App\Http\Controllers\Api;

use App\Contracts\Services\DiagnosisServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Diagnosis\AnalyzeDiagnosisRequest;

class DiagnosisController extends Controller
{
    public function __construct(
        private readonly DiagnosisServiceInterface $diagnosisService
    ) {
    }

    public function analyze(AnalyzeDiagnosisRequest $request)
    {
        $validated = $request->validated();
        $description = trim((string) ($validated['description'] ?? ''));
        $image = $request->file('image');

        try {
            $result = $this->diagnosisService->analyze(
                user: $request->user('sanctum') ?? $request->user(),
                description: $description,
                image: $image,
                age: $validated['age'] ?? null,
                gender: $validated['gender'] ?? null
            );
        } catch (\Throwable $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json($result);
    }
}
