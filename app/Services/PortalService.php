<?php

namespace App\Services;

use App\Contracts\Services\PortalServiceInterface;
use App\Handlers\Portal\PortalHandler;
use App\Models\User;

class PortalService implements PortalServiceInterface
{
    public function __construct(
        private readonly PortalHandler $handler
    ) {
    }

    public function getPortalData(User $user): array
    {
        $allAnalyses    = $this->handler->getAllAnalyses($user);
        $recentAnalyses = $this->handler->getRecentAnalyses($user);

        $last = $recentAnalyses->first();

        $modelName = function ($item): string {
            $payload = is_array($item->response_payload) ? $item->response_payload : [];
            $model = trim((string) ($payload['ai_model'] ?? ''));
            if ($model !== '') {
                return $model;
            }

            $source = trim((string) ($payload['source'] ?? ''));

            return $source !== '' ? $source : 'unknown_model';
        };

        $requestsByModel = $allAnalyses
            ->groupBy($modelName)
            ->map(fn ($items, $model) => [
                'model' => $model,
                'count' => $items->count(),
            ])
            ->sortByDesc('count')
            ->values();

        $requestsByModelByDay = $allAnalyses
            ->groupBy(fn ($item) => $item->created_at?->format('Y-m-d') ?? 'unknown')
            ->map(function ($items, $day) use ($modelName) {
                $models = $items
                    ->groupBy($modelName)
                    ->map(fn ($group, $model) => [
                        'model' => $model,
                        'count' => $group->count(),
                    ])
                    ->values();

                return [
                    'day' => $day,
                    'models' => $models,
                ];
            })
            ->sortBy('day')
            ->values();

        return [
            'quick_actions' => [
                ['id' => 'start_analysis', 'title' => 'Начать анализ', 'route' => '/analyze'],
                ['id' => 'my_analyses', 'title' => 'Мои анализы', 'route' => '/history'],
                ['id' => 'profile', 'title' => 'Профиль', 'route' => '/profile'],
            ],
            'stats' => [
                'analyses_count'   => $this->handler->getAnalysesCount($user),
                'last_analysis_at' => $last?->created_at,
                'last_diagnosis'   => $last?->response_payload['diagnosis'] ?? null,
            ],
            'recent_analyses' => $recentAnalyses,
            'chart_series' => [
                'requests_by_model'        => $requestsByModel,
                'model_share'              => $requestsByModel,
                'requests_by_model_by_day' => $requestsByModelByDay,
            ],
        ];
    }
}
