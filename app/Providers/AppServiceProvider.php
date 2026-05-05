<?php

namespace App\Providers;

use App\Application\Diagnosis\Queries\AnalyzeDiagnosisHandler;
use App\Application\Diagnosis\Queries\AnalyzeDiagnosisQuery;
use App\Application\Shared\QueryBus\InMemoryQueryBus;
use App\Application\Shared\QueryBus\QueryBus;
use App\Contracts\Services\AuthServiceInterface;
use App\Contracts\Services\DiagnosisHistoryServiceInterface;
use App\Contracts\Services\DiagnosisServiceInterface;
use App\Contracts\Services\PortalServiceInterface;
use App\Contracts\Services\ProfileServiceInterface;
use App\Services\AuthService;
use App\Services\DiagnosisHistoryService;
use App\Services\DiagnosisService;
use App\Services\PortalService;
use App\Services\ProfileService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
        $this->app->bind(DiagnosisServiceInterface::class, DiagnosisService::class);
        $this->app->bind(DiagnosisHistoryServiceInterface::class, DiagnosisHistoryService::class);
        $this->app->bind(PortalServiceInterface::class, PortalService::class);
        $this->app->bind(ProfileServiceInterface::class, ProfileService::class);

        $this->app->singleton(QueryBus::class, function ($app): QueryBus {
            return new InMemoryQueryBus($app, [
                AnalyzeDiagnosisQuery::class => AnalyzeDiagnosisHandler::class,
            ]);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
