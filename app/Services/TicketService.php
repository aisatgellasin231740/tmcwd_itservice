<?php

namespace App\Services;

use App\Models\Attachment;
use App\Models\Comment;
use App\Models\Priority;
use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\User;
use App\Notifications\TicketAssigned;
use App\Notifications\TicketCommented;
use App\Notifications\TicketCreated;
use App\Notifications\TicketStatusUpdated;
use App\Notifications\UrgentTicketAlert;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TicketService
{
    public function __construct(private readonly SlaService $slaService)
    {
    }

    // ── Ticket Creation ────────────────────────────────────────

    /**
     * Generate a unique ticket number: TMCWD-YYYY-#####
     * Must be called inside a DB transaction with lockForUpdate.
     */
    public function generateTicketNumber(): string
    {
        $year = now()->year;
        $last = Ticket::whereYear('created_at', $year)
                      ->lockForUpdate()
                      ->orderByDesc('id')
                      ->first();

        $seq = 1;
        if ($last) {
            // Extract the numeric part after the last dash
            $parts = explode('-', $last->ticket_number);
            $seq   = (int) end($parts) + 1;
        }

        return 'TMCWD-' . $year . '-' . str_pad($seq, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Create a new ticket and fire notifications.
     */
    public function create(array $validated, User $requester): Ticket
    {
        return DB::transaction(function () use ($validated, $requester) {
            $priority = Priority::findOrFail($validated['priority_id']);

            $ticket = Ticket::create([
                'ticket_number' => $this->generateTicketNumber(),
                'title'         => $validated['title'],
                'description'   => $validated['description'],
                'requester_id'  => $requester->id,
                'department_id' => $validated['department_id'],
                'category_id'   => $validated['category_id'],
                'priority_id'   => $validated['priority_id'],
                'status'        => 'open',
                'sla_due_at'    => $this->slaService->calculateDueAt($priority, now()),
            ]);

            TicketActivity::create([
                'ticket_id'   => $ticket->id,
                'user_id'     => $requester->id,
                'action'      => 'created',
                'description' => 'Ticket submitted by ' . $requester->name,
            ]);

            // Notifications
            $requester->notify(new TicketCreated($ticket));

            if (strtolower($priority->name) === 'urgent') {
                $recipients = User::role(['it_head', 'it_staff'])->where('id', '!=', $requester->id)->get();
                foreach ($recipients as $recipient) {
                    $recipient->notify(new UrgentTicketAlert($ticket));
                }
            }

            return $ticket;
        });
    }

    // ── Status Management ──────────────────────────────────────

    /**
     * Update ticket status, manage SLA, log activity, send notifications.
     */
    public function updateStatus(Ticket $ticket, string $newStatus, User $actor): void
    {
        $oldStatus = $ticket->status;

        if ($oldStatus === $newStatus) {
            return;
        }

        DB::transaction(function () use ($ticket, $newStatus, $oldStatus, $actor) {
            // SLA pause/resume logic
            if ($newStatus === 'on_hold') {
                $this->slaService->pause($ticket);
            } elseif ($oldStatus === 'on_hold') {
                $this->slaService->resume($ticket);
            }

            // Set timestamps
            $now = now();
            if ($newStatus === 'resolved') {
                $ticket->resolved_at = $now;
            } elseif ($newStatus === 'closed') {
                $ticket->closed_at = $now;
            } elseif (in_array($oldStatus, ['resolved', 'closed']) && $newStatus === 'open') {
                // Reopened
                $ticket->resolved_at = null;
                $ticket->closed_at   = null;
            }

            $ticket->status = $newStatus;
            $ticket->save();

            TicketActivity::create([
                'ticket_id'  => $ticket->id,
                'user_id'    => $actor->id,
                'action'     => 'status_changed',
                'old_value'  => $oldStatus,
                'new_value'  => $newStatus,
                'description' => "{$actor->name} changed status from \"" .
                    $this->statusLabel($oldStatus) . "\" to \"" .
                    $this->statusLabel($newStatus) . '"',
            ]);

            // Notify requester
            $ticket->requester->notify(new TicketStatusUpdated($ticket, $oldStatus, $newStatus));
        });
    }

    /**
     * Explicit requester reopen with a mandatory reason.
     *
     * Sets status back to 'open', clears resolved/closed timestamps,
     * logs a dedicated 'reopened' activity entry, and posts the reason
     * as a public comment so IT staff can see it in the conversation thread.
     */
    public function reopen(Ticket $ticket, User $requester, string $reason): void
    {
        DB::transaction(function () use ($ticket, $requester, $reason) {
            // Clear resolution state
            $ticket->status      = 'open';
            $ticket->resolved_at = null;
            $ticket->closed_at   = null;
            $ticket->save();

            // Dedicated activity log entry
            TicketActivity::create([
                'ticket_id'   => $ticket->id,
                'user_id'     => $requester->id,
                'action'      => 'reopened',
                'old_value'   => 'resolved',
                'new_value'   => 'open',
                'description' => $requester->name . ' marked this ticket as not resolved and requested it to be reopened.',
            ]);

            // Post the reason as a visible public comment
            Comment::create([
                'ticket_id'   => $ticket->id,
                'user_id'     => $requester->id,
                'body'        => '🔁 **Reopen reason:** ' . $reason,
                'is_internal' => false,
            ]);

            // Notify the assignee (if any) that the ticket has been reopened
            if ($ticket->assigned_to) {
                $ticket->assignee?->notify(new TicketStatusUpdated($ticket, 'resolved', 'open'));
            }
        });
    }

    // ── Assignment ─────────────────────────────────────────────

    public function assign(Ticket $ticket, ?int $agentId, User $actor): void
    {
        $oldAssignee = $ticket->assignee?->name ?? 'Unassigned';
        $ticket->assigned_to = $agentId;
        $ticket->save();

        $newAgent = $agentId ? User::find($agentId) : null;
        $newLabel = $newAgent?->name ?? 'Unassigned';

        TicketActivity::create([
            'ticket_id'   => $ticket->id,
            'user_id'     => $actor->id,
            'action'      => 'assigned',
            'old_value'   => $oldAssignee,
            'new_value'   => $newLabel,
            'description' => "{$actor->name} assigned ticket to {$newLabel}",
        ]);

        if ($newAgent && $newAgent->id !== $actor->id) {
            $newAgent->notify(new TicketAssigned($ticket));
        }
    }

    /**
     * Staff-to-staff reassignment: actor must currently own the ticket.
     * Logs a distinct "reassigned" activity and notifies the new assignee.
     */
    public function reassign(Ticket $ticket, int $newAgentId, User $actor): void
    {
        $fromName = $actor->name;
        $toAgent  = User::findOrFail($newAgentId);

        $ticket->assigned_to = $toAgent->id;
        $ticket->save();

        TicketActivity::create([
            'ticket_id'   => $ticket->id,
            'user_id'     => $actor->id,
            'action'      => 'reassigned',
            'old_value'   => $fromName,
            'new_value'   => $toAgent->name,
            'description' => "{$actor->name} reassigned ticket from {$fromName} to {$toAgent->name}",
        ]);

        $toAgent->notify(new TicketAssigned($ticket));
    }

    // ── Comments ───────────────────────────────────────────────

    /**
     * Add a comment. If requester replies on a resolved ticket, auto-reopen.
     * Optionally store file attachments linked to this comment.
     *
     * @param  \Illuminate\Http\UploadedFile[]  $files
     */
    public function addComment(Ticket $ticket, User $user, string $body, bool $isInternal = false, array $files = []): Comment
    {
        // Auto-reopen if requester replies on resolved ticket
        if ($user->hasRole('requester') && $ticket->isResolved()) {
            $this->updateStatus($ticket, 'open', $user);
        }

        $comment = Comment::create([
            'ticket_id'   => $ticket->id,
            'user_id'     => $user->id,
            'body'        => $body,
            'is_internal' => $isInternal,
        ]);

        // Store any attached files linked to this comment
        if (! empty($files)) {
            foreach ($files as $file) {
                $dir        = 'attachments/' . $ticket->id;
                $uniqueName = uniqid() . '_' . $file->getClientOriginalName();
                $storedPath = Storage::disk('local')->putFileAs($dir, $file, $uniqueName);

                \App\Models\Attachment::create([
                    'ticket_id'     => $ticket->id,
                    'comment_id'    => $comment->id,
                    'user_id'       => $user->id,
                    'original_name' => $file->getClientOriginalName(),
                    'stored_path'   => $storedPath,
                    'mime_type'     => $file->getMimeType(),
                    'size'          => $file->getSize(),
                ]);
            }
        }

        if (! $isInternal) {
            TicketActivity::create([
                'ticket_id'   => $ticket->id,
                'user_id'     => $user->id,
                'action'      => 'comment_added',
                'description' => $user->name . ' added a reply' . (! empty($files) ? ' with ' . count($files) . ' attachment(s)' : ''),
            ]);

            // Notify: if agent replied → notify requester; if requester replied → notify assignee
            if ($user->hasAnyRole(['it_staff', 'it_head'])) {
                $ticket->requester->notify(new TicketCommented($ticket, $comment));
            } elseif ($ticket->assigned_to) {
                $ticket->assignee?->notify(new TicketCommented($ticket, $comment));
            }
        }

        return $comment;
    }

    // ── Attachments ────────────────────────────────────────────

    /**
     * Store uploaded files and create Attachment records.
     *
     * @param  UploadedFile[]  $files
     */
    public function storeAttachments(Ticket $ticket, array $files, User $uploader): void
    {
        foreach ($files as $file) {
            $dir        = 'attachments/' . $ticket->id;
            $uniqueName = uniqid() . '_' . $file->getClientOriginalName();
            $storedPath = Storage::disk('local')->putFileAs($dir, $file, $uniqueName);

            Attachment::create([
                'ticket_id'     => $ticket->id,
                'user_id'       => $uploader->id,
                'original_name' => $file->getClientOriginalName(),
                'stored_path'   => $storedPath,
                'mime_type'     => $file->getMimeType(),
                'size'          => $file->getSize(),
            ]);

            TicketActivity::create([
                'ticket_id'   => $ticket->id,
                'user_id'     => $uploader->id,
                'action'      => 'attachment_added',
                'new_value'   => $file->getClientOriginalName(),
                'description' => $uploader->name . ' uploaded ' . $file->getClientOriginalName(),
            ]);
        }
    }

    // ── Helpers ────────────────────────────────────────────────

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'open'        => 'Open',
            'in_progress' => 'In Progress',
            'on_hold'     => 'On Hold',
            'resolved'    => 'Resolved',
            'closed'      => 'Closed',
            default       => ucfirst($status),
        };
    }
}
