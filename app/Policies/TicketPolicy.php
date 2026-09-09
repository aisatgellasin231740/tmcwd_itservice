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

    /**
     * Requester can edit their own ticket only while it is still "open".
     * IT staff / head can always update via their own update policy.
     */
    public function editOwn(User $user, Ticket $ticket): bool
    {
        return $ticket->requester_id === $user->id
            && $ticket->status === 'open';
    }

    /**
     * Requester can cancel (withdraw) their own ticket only while it is still "open".
     */
    public function cancel(User $user, Ticket $ticket): bool
    {
        return $ticket->requester_id === $user->id
            && $ticket->status === 'open';
    }

    /**
     * Requester can explicitly reopen their own ticket only when it is Resolved.
     * Closed tickets cannot be reopened — the requester must submit a new ticket.
     */
    public function reopen(User $user, Ticket $ticket): bool
    {
        return $ticket->requester_id === $user->id
            && $ticket->status === 'resolved';
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

    /**
     * IT Staff can reassign a ticket that is currently assigned to them.
     * IT Head can always reassign any ticket.
     */
    public function reassign(User $user, Ticket $ticket): bool
    {
        if ($user->hasRole('it_head')) {
            return true;
        }
        // IT Staff: only tickets currently assigned to themselves
        return $user->hasRole('it_staff') && $ticket->assigned_to === $user->id;
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
