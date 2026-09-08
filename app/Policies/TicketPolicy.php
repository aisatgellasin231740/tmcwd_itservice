<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    /** Admin/agent can view any ticket; requester only their own */
    public function viewAny(User $user): bool
    {
        return true; // controller scopes the query by role
    }

    public function view(User $user, Ticket $ticket): bool
    {
        if ($user->hasAnyRole(['it_head', 'it_staff'])) {
            return true;
        }
        return $ticket->requester_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true; // all authenticated users can create
    }

    /** Only agents and admins can update ticket metadata */
    public function update(User $user, Ticket $ticket): bool
    {
        return $user->hasAnyRole(['it_head', 'it_staff']);
    }

    /** Only admins can delete tickets */
    public function delete(User $user, Ticket $ticket): bool
    {
        return $user->hasRole('it_head');
    }

    /** Requesters can comment only on their own tickets; agents/admins on all */
    public function addComment(User $user, Ticket $ticket): bool
    {
        if ($user->hasAnyRole(['it_head', 'it_staff'])) {
            return true;
        }
        // Requester: own ticket and ticket not closed
        return $ticket->requester_id === $user->id && ! $ticket->isClosed();
    }

    /** Only agents and admins can add internal notes */
    public function addInternalNote(User $user, Ticket $ticket): bool
    {
        return $user->hasAnyRole(['it_head', 'it_staff']);
    }

    /** IT Head can assign to anyone. IT Staff can only self-assign (enforced in controller). */
    public function assign(User $user, Ticket $ticket): bool
    {
        return $user->hasAnyRole(['it_head', 'it_staff']);
    }

    /** Only agents and admins can change ticket status */
    public function changeStatus(User $user, Ticket $ticket): bool
    {
        return $user->hasAnyRole(['it_head', 'it_staff']);
    }

    /** Only admins can change priority */
    public function changePriority(User $user, Ticket $ticket): bool
    {
        return $user->hasRole('it_head');
    }
}
