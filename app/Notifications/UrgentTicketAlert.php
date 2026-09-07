<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UrgentTicketAlert extends Notification
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

        $excerpt = mb_strimwidth($this->ticket->description, 0, 200, '…');

        return (new MailMessage)
            ->subject("⚠️ URGENT Ticket: {$this->ticket->ticket_number} — {$this->ticket->title}")
            ->greeting('⚠️ Urgent Ticket Alert')
            ->line("An **URGENT** priority ticket has been submitted and requires immediate attention.")
            ->line("**Ticket:** {$this->ticket->ticket_number}")
            ->line("**Title:** {$this->ticket->title}")
            ->line("**Submitted by:** {$this->ticket->requester->name} ({$this->ticket->department->name})")
            ->line("**Category:** {$this->ticket->category->name}")
            ->line("**SLA Target:** 2 hours")
            ->line("**Description:** {$excerpt}")
            ->action('Open Ticket Now', $url)
            ->salutation('TMCWD IT Request Service');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'          => 'urgent_alert',
            'ticket_id'     => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'title'         => $this->ticket->title,
            'requester'     => $this->ticket->requester->name,
            'message'       => "⚠️ URGENT ticket {$this->ticket->ticket_number} submitted: {$this->ticket->title}",
        ];
    }
}
