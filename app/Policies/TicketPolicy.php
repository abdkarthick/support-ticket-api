<?php
namespace App\Policies;
use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    public function view(User $user, Ticket $ticket): bool
    {
        // Admin, Agent can view all, Customer can view own only
        return $user->isAgent() || $user->id === $ticket->user_id;
    }

    public function update(User $user, Ticket $ticket): bool
    {
        // Admin, Agent can update all, Customer can update own only
        return $user->isAgent() || $user->id === $ticket->user_id;
    }

    public function delete(User $user, Ticket $ticket): bool
    {
        // Only owner or admin can delete
        return $user->isAdmin() || $user->id === $ticket->user_id;
    }
}