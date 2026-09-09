<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    protected $fillable = [
        'ticket_number',
        'title',
        'description',
        'requester_id',
        'department_id',
        'category_id',
        'priority_id',
        'assigned_to',
        'status',
        'sla_due_at',
        'sla_paused_at',
        'sla_paused_seconds',
        'resolved_at',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'sla_due_at'    => 'datetime',
            'sla_paused_at' => 'datetime',
            'resolved_at'   => 'datetime',
            'closed_at'     => 'datetime',
            'sla_paused_seconds' => 'integer',
        ];
    }

    // ── Relationships ──────────────────────────────────────────

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class)->withTrashed();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class)->withTrashed();
    }

    public function priority(): BelongsTo
    {
        return $this->belongsTo(Priority::class)->withTrashed();
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->orderBy('created_at');
    }

    public function publicComments(): HasMany
    {
        return $this->hasMany(Comment::class)
                    ->where('is_internal', false)
                    ->orderBy('created_at');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(TicketActivity::class)->orderBy('created_at');
    }

    // ── Scopes ─────────────────────────────────────────────────

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', 'open');
    }

    public function scopeUnassigned(Builder $query): Builder
    {
        return $query->whereNull('assigned_to');
    }

    public function scopeAssignedTo(Builder $query, int $userId): Builder
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeForRequester(Builder $query, int $userId): Builder
    {
        return $query->where('requester_id', $userId);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotIn('status', ['resolved', 'closed']);
    }

    // ── Status helpers ─────────────────────────────────────────

    public function isOpen(): bool        { return $this->status === 'open'; }
    public function isInProgress(): bool  { return $this->status === 'in_progress'; }
    public function isOnHold(): bool      { return $this->status === 'on_hold'; }
    public function isResolved(): bool    { return $this->status === 'resolved'; }
    public function isClosed(): bool      { return $this->status === 'closed'; }
    public function isFinished(): bool    { return in_array($this->status, ['resolved', 'closed']); }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'open'        => 'Open',
            'in_progress' => 'In Progress',
            'on_hold'     => 'On Hold',
            'resolved'    => 'Resolved',
            'closed'      => 'Closed',
            default       => ucfirst($this->status),
        };
    }

    public function statusBadgeClasses(): string
    {
        return match ($this->status) {
            'open'        => 'bg-blue-100 text-blue-800',
            'in_progress' => 'bg-yellow-100 text-yellow-800',
            'on_hold'     => 'bg-gray-100 text-gray-700',
            'resolved'    => 'bg-green-100 text-green-800',
            'closed'      => 'bg-gray-300 text-gray-600',
            default       => 'bg-gray-100 text-gray-700',
        };
    }

    // ── SLA accessor (delegated to SlaService) ─────────────────

    public function getSlaStatusAttribute(): string
    {
        return app(\App\Services\SlaService::class)->getStatus($this);
    }
}
