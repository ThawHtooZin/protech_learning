<?php

namespace App\Providers;

use App\Models\Team;
use App\Services\MentionRenderer;
use App\Services\TeamAccessService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(MentionRenderer::class, fn () => new MentionRenderer);
    }

    public function boot(): void
    {
        View::composer(['layouts.learn', 'layouts.team', 'partials.learn-header'], function ($view): void {
            if (auth()->check()) {
                $view->with(
                    'unreadNotificationCount',
                    auth()->user()->unreadNotifications()->count()
                );
            } else {
                $view->with('unreadNotificationCount', 0);
            }
        });

        // Once per browser session: surface latest unread notification calmly.
        View::composer(['layouts.learn', 'layouts.team'], function ($view): void {
            $view->with('notiCatchup', null);

            if (! auth()->check()) {
                return;
            }

            if (session()->has('noti_catchup_shown')) {
                return;
            }

            $latest = auth()->user()->unreadNotifications()->latest()->first();
            session(['noti_catchup_shown' => true]);

            if ($latest) {
                $view->with('notiCatchup', $latest);
            }
        });

        View::composer('teams._sidebar', function ($view): void {
            $team = $view->getData()['team'] ?? null;
            if (! $team instanceof Team) {
                return;
            }

            if (! $team->relationLoaded('channels')) {
                $team->load(['channels' => fn ($q) => $q->orderByDesc('is_general')->orderBy('sort_order')->orderBy('name')]);
            }

            $user = auth()->user();
            $access = app(TeamAccessService::class);

            $view->with([
                'sidebarChannels' => $team->channels,
                'canManageTeam' => $user ? $access->canManageTeam($user, $team) : false,
            ]);
        });
    }
}
