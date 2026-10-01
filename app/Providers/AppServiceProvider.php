<?php

namespace App\Providers;

use App\Events\TicketAssigned;
use App\Events\TicketCreated;
use App\Events\TicketReplied;
use App\Events\TicketStatusChanged;
use App\Listeners\LogStatusChange;
use App\Listeners\LogTicketActivity;
use App\Listeners\SendTicketAssignedNotification;
use App\Listeners\SendTicketRepliedNotification;
use App\Models\Ticket;
use App\Policies\TicketPolicy;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);
        Gate::policy(Ticket::class, TicketPolicy::class);

        // Event Listeners
        Event::listen(TicketCreated::class, LogTicketActivity::class);
        Event::listen(TicketAssigned::class, LogTicketActivity::class);
        Event::listen(TicketAssigned::class, SendTicketAssignedNotification::class);
        Event::listen(TicketReplied::class, LogTicketActivity::class);
        Event::listen(TicketReplied::class, SendTicketRepliedNotification::class);
        Event::listen(TicketStatusChanged::class, LogStatusChange::class);
    }
}
