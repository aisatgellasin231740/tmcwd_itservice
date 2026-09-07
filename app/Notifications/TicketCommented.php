<?php

namespace App\Notifications;

use App\Models\Comment;
use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketCommented extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Ticket  $ticket,
        public readonly Comment $comment,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $author = $this->comment->user->name;
        $route  = $notifiable->hasAnyRole(['it_staff', 'it_head'])
            ? route('agent.tickets.show', $this->ticket)
            : route('requester.tickets.show', $this->ticket);

        $excerpt = mb_strimwidth(strip_tags($this->comment->body), 0, 120, '…');

        return (new MailMessage)
            ->subject("New Reply on Ticket [{$this->ticket->ticket_number}]")
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line("**{$author}** replied to ticket **{$this->ticket->ticket_number}**.")
            ->line("**Title:** {$this->ticket->title}")
            ->line("**Reply:** {$excerpt}")
            ->action('View Ticket', $route)
            ->salutation('TMCWD IT Request Service');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'          => 'ticket_commented',
            'ticket_id'     => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'comment_id'    => $this->comment->id,
            'author'        => $this->comment->user->name,
            'message'       => "{$this->comment->user->name} replied on ticket {$this->ticket->ticket_number}.",
        ];
    }
}
