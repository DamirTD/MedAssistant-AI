<?php

namespace App\Services;

use App\Application\Diagnosis\Queries\AnalyzeDiagnosisQuery;
use App\Application\Shared\QueryBus\QueryBus;
use App\Contracts\Services\DiagnosisServiceInterface;
use App\Handlers\Diagnosis\DiagnosisHandler;
use App\Models\User;
use Illuminate\Http\UploadedFile;

class DiagnosisService implements DiagnosisServiceInterface
{
    public function __construct(
        private readonly QueryBus $queryBus,
        private readonly DiagnosisHandler $handler
    ) {
    }

    public function analyze(?User $user, string $description, ?UploadedFile $image, ?int $age, ?string $gender): array
    {
        $result = $this->queryBus->ask(
            new AnalyzeDiagnosisQuery(
                description: $description !== '' ? $description : 'Описание не указано',
                image: $image,
                age: $age,
                gender: $gender
            )
        );

        if ($user) {
            $this->handler->storeHistory($user, [
                'description'     => $description !== '' ? $description : null,
                'age'             => $age,
                'gender'          => $gender,
                'has_image'       => (bool) $image,
                'request_payload' => [
                    'description' => $description !== '' ? $description : null,
                    'age'         => $age,
                    'gender'      => $gender,
                    'has_image'   => (bool) $image,
                ],
                'response_payload' => $result,
            ]);
        }

        return $result;
    }
}
