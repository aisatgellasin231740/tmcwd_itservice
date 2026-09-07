<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketCreated extends Notification
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
        $url = route('requester.tickets.show', $this->ticket);

        return (new MailMessage)
            ->subject("Ticket [{$this->ticket->ticket_number}] Created — TMCWD IT Service")
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line("Your IT support request has been received and assigned ticket number **{$this->ticket->ticket_number}**.")
            ->line("**Title:** {$this->ticket->title}")
            ->line("**Category:** {$this->ticket->category->name}")
            ->line("**Priority:** {$this->ticket->priority->name}")
            ->action('View Your Ticket', $url)
            ->line('Our IT team will review your request shortly.')
            ->salutation('TMCWD IT Request Service');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'          => 'ticket_created',
            'ticket_id'     => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'title'         => $this->ticket->title,
            'message'       => "Your ticket {$this->ticket->ticket_number} has been created.",
        ];
    }
}
