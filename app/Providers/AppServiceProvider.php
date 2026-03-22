<?php

namespace App\Providers;

use App\Services\MentionRenderer;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(MentionRenderer::class, fn () => new MentionRenderer);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.learn', function ($view): void {
            if (auth()->check()) {
                $view->with(
                    'unreadNotificationCount',
                    auth()->user()->unreadNotifications()->count()
                );
            } else {
                $view->with('unreadNotificationCount', 0);
            }
        });
    }
}
