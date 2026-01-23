<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Comment;
use App\Models\Ticket;
use App\Observers\CommentObserver;
use App\Observers\TicketObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->environment('local') && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::shouldBeStrict(! $this->app->environment('production'));

        Ticket::observe(TicketObserver::class);
        Comment::observe(CommentObserver::class);
    }
}
