<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketStatusUpdated extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Ticket $ticket,
        public readonly string $oldStatus,
        public readonly string $newStatus,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $statusLabel = match ($this->newStatus) {
            'in_progress' => 'In Progress',
            'on_hold'     => 'On Hold',
            'resolved'    => 'Resolved',
            'closed'      => 'Closed',
            default       => ucfirst($this->newStatus),
        };

        $url = route('requester.tickets.show', $this->ticket);

        return (new MailMessage)
            ->subject("Ticket [{$this->ticket->ticket_number}] Status Updated — {$statusLabel}")
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line("The status of your ticket **{$this->ticket->ticket_number}** has been updated.")
            ->line("**Title:** {$this->ticket->title}")
            ->line("**New Status:** {$statusLabel}")
            ->action('View Ticket', $url)
            ->salutation('TMCWD IT Request Service');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'          => 'status_updated',
            'ticket_id'     => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'old_status'    => $this->oldStatus,
            'new_status'    => $this->newStatus,
            'message'       => "Ticket {$this->ticket->ticket_number} status changed to {$this->newStatus}.",
        ];
    }
}
