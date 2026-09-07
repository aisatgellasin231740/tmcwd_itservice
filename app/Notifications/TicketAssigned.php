<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketAssigned extends Notification
{
    use Queueable;

    public function __construct(public readonly Ticket $ticket)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('agent.tickets.show', $this->ticket);

        return (new MailMessage)
            ->subject("Ticket [{$this->ticket->ticket_number}] Assigned to You")
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line("A ticket has been assigned to you.")
            ->line("**Ticket:** {$this->ticket->ticket_number}")
            ->line("**Title:** {$this->ticket->title}")
            ->line("**Priority:** {$this->ticket->priority->name}")
            ->line("**Department:** {$this->ticket->department->name}")
            ->line("**SLA Due:** " . ($this->ticket->sla_due_at?->format('M d, Y H:i') ?? 'N/A'))
            ->action('Open Ticket', $url)
            ->salutation('TMCWD IT Request Service');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'          => 'ticket_assigned',
            'ticket_id'     => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'title'         => $this->ticket->title,
            'message'       => "Ticket {$this->ticket->ticket_number} has been assigned to you.",
        ];
    }
}
