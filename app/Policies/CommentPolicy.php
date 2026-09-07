<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\Ticket;
use App\Models\User;

class CommentPolicy
{
    /** Requesters must not see internal notes */
    public function view(User $user, Comment $comment): bool
    {
        if ($user->hasAnyRole(['it_head', 'it_staff'])) {
            return true;
        }
        // Requester: only public comments on their own tickets
        return ! $comment->is_internal
            && $comment->ticket->requester_id === $user->id;
    }

    /** Controls who can post what type of comment */
    public function create(User $user, Ticket $ticket): bool
    {
        if ($user->hasAnyRole(['it_head', 'it_staff'])) {
            return true;
        }
        // Requester: own ticket, not closed
        return $ticket->requester_id === $user->id && ! $ticket->isClosed();
    }
}
