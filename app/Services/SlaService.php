<?php

namespace App\Services;

use App\Models\Priority;
use App\Models\Ticket;
use Carbon\Carbon;

class SlaService
{
    /**
     * Calculate the SLA due date/time from a given start time and priority.
     */
    public function calculateDueAt(Priority $priority, Carbon $startedAt): Carbon
    {
        return (clone $startedAt)->addMinutes((int) ($priority->sla_hours * 60));
    }

    /**
     * Pause the SLA clock (ticket goes On Hold).
     */
    public function pause(Ticket $ticket): void
    {
        if ($ticket->sla_paused_at) {
            return; // already paused
        }
        $ticket->sla_paused_at = now();
        $ticket->saveQuietly();
    }

    /**
     * Resume the SLA clock (ticket leaves On Hold).
     * Extends sla_due_at by the duration it was paused.
     */
    public function resume(Ticket $ticket): void
    {
        if (! $ticket->sla_paused_at) {
            return; // not paused
        }

        $pausedSeconds = (int) now()->diffInSeconds($ticket->sla_paused_at);
        $ticket->sla_paused_seconds += $pausedSeconds;
        $ticket->sla_due_at         = $ticket->sla_due_at
            ? (clone $ticket->sla_due_at)->addSeconds($pausedSeconds)
            : null;
        $ticket->sla_paused_at = null;
        $ticket->saveQuietly();
    }

    /**
     * Get the current SLA status string.
     *
     * Returns: 'ok' | 'warning' | 'breached' | 'paused' | 'met'
     */
    public function getStatus(Ticket $ticket): string
    {
        if (! $ticket->sla_due_at) {
            return 'ok';
        }

        // Finished tickets: check if they met SLA
        if ($ticket->isFinished()) {
            $finishedAt = $ticket->resolved_at ?? $ticket->closed_at;
            if ($finishedAt && $finishedAt->lte($ticket->sla_due_at)) {
                return 'met';
            }
            return 'breached';
        }

        // Paused
        if ($ticket->sla_paused_at) {
            return 'paused';
        }

        $now       = now();
        $dueAt     = $ticket->sla_due_at;
        $createdAt = $ticket->created_at;

        // Already breached
        if ($now->gt($dueAt)) {
            return 'breached';
        }

        // Warning: less than 20% of total SLA time remaining
        $totalSeconds     = $createdAt->diffInSeconds($dueAt);
        $remainingSeconds = $now->diffInSeconds($dueAt);

        if ($totalSeconds > 0 && ($remainingSeconds / $totalSeconds) < 0.20) {
            return 'warning';
        }

        return 'ok';
    }

    /**
     * Human-readable time remaining or overdue label.
     */
    public function getTimeLabel(Ticket $ticket): string
    {
        if (! $ticket->sla_due_at) {
            return '—';
        }

        $status = $this->getStatus($ticket);

        if ($status === 'met') {
            return 'Met ✓';
        }

        if ($status === 'paused') {
            return 'Paused';
        }

        $now   = now();
        $dueAt = $ticket->sla_due_at;

        if ($status === 'breached') {
            $diff = $now->diff($dueAt);
            if ($diff->days > 0) {
                return "Overdue by {$diff->days}d {$diff->h}h";
            }
            return "Overdue by {$diff->h}h {$diff->i}m";
        }

        // ok or warning
        $diff = $now->diff($dueAt);
        if ($diff->days > 0) {
            return "{$diff->days}d {$diff->h}h left";
        }
        if ($diff->h > 0) {
            return "{$diff->h}h {$diff->i}m left";
        }
        return "{$diff->i}m left";
    }
}
