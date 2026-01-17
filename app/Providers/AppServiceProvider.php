<?php

namespace App\Providers;

use App\Models\Comment;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::shouldBeStrict(! $this->app->isProduction());

        Ticket::observe(TicketObserver::class);
        Comment::observe(CommentObserver::class);
    }
}
