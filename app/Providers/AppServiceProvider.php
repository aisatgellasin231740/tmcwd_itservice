<?php

namespace App\Providers;

use App\Models\Comment;
use App\Models\Ticket;
use App\Policies\CommentPolicy;
use App\Policies\TicketPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Register policies
        Gate::policy(Ticket::class, TicketPolicy::class);
        Gate::policy(Comment::class, CommentPolicy::class);

        // Admin bypasses all gates
        Gate::before(function ($user, $ability) {
            if ($user->hasRole('it_head')) {
                return true;
            }
        });
    }
}
