<?php

namespace App\Providers;

use App\Models\Invoice;
use App\Observers\InvoiceObserver;
use App\Services\Calculations\InvoiceCalculationService;
use App\Services\CreditScoreService;
use App\Services\DocumentProcessingService;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(CreditScoreService::class, function ($app) {
            return new CreditScoreService();
        });

        $this->app->singleton(DocumentProcessingService::class, function ($app) {
            return new DocumentProcessingService();
        });

        $this->app->singleton(InvoiceCalculationService::class, function ($app) {
            return new InvoiceCalculationService();
        });

        $this->mergeConfigFrom(
            __DIR__ . '/../../config/afrimark.php', 'afrimark'
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Invoice::observe(InvoiceObserver::class);

        $this->app['events']->listen('Illuminate\Auth\Events\Login', function ($event) {
            $creditScoreService = app(CreditScoreService::class);
            $creditScoreService->fetchUserCreditScores($event->user);
        });

        // Blade components for document manager
//        Blade::componentNamespace('App\\View\\Components\\Filament\\Client', 'filament.client.components');

        // anonymous Blade components
//        Blade::anonymousComponentNamespace('filament/client/components', 'filament.client.components');

        $this->publishes([
            __DIR__ . '/../../config/afrimark.php' => config_path('afrimark.php'),
        ], 'afrimark-config');
    }
}
