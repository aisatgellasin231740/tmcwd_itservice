<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketActivity extends Model
{
    protected $fillable = [
        'ticket_id',
        'user_id',
        'action',
        'old_value',
        'new_value',
        'description',
    ];

    // ── Relationships ──────────────────────────────────────────

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Helpers ────────────────────────────────────────────────

    /** Human-readable activity label for the timeline */
    public function getDescriptionLabelAttribute(): string
    {
        if ($this->description) {
            return $this->description;
        }

        return match ($this->action) {
            'created'         => 'Ticket submitted',
            'status_changed'  => "Status changed from \"{$this->old_value}\" to \"{$this->new_value}\"",
            'assigned'        => $this->new_value
                                    ? "Assigned to {$this->new_value}"
                                    : 'Unassigned',
            'priority_changed'=> "Priority changed from \"{$this->old_value}\" to \"{$this->new_value}\"",
            'comment_added'   => 'Comment added',
            'attachment_added'=> 'Attachment uploaded',
            default           => ucfirst(str_replace('_', ' ', $this->action)),
        };
    }

    /** Icon name for timeline display */
    public function getIconAttribute(): string
    {
        return match ($this->action) {
            'created'          => 'plus-circle',
            'status_changed'   => 'arrow-path',
            'assigned'         => 'user',
            'priority_changed' => 'flag',
            'comment_added'    => 'chat-bubble-left',
            'attachment_added' => 'paper-clip',
            default            => 'circle-stack',
        };
    }
}
