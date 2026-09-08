<?php

namespace App\Http\Controllers;

use App\Models\Priority;
use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\User;
use App\Services\TicketService;
use Illuminate\Http\Request;

class BulkTicketController extends Controller
{
    public function __construct(private readonly TicketService $ticketService)
    {
    }

    public function apply(Request $request)
    {
        $request->validate([
            'ticket_ids'  => ['required', 'array', 'min:1'],
            'ticket_ids.*'=> ['integer', 'exists:tickets,id'],
            'action'      => ['required', 'string', 'in:assign,close,resolve,priority'],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
            'priority_id' => ['nullable', 'integer', 'exists:priorities,id'],
        ]);

        $tickets = Ticket::whereIn('id', $request->ticket_ids)->get();
        $actor   = $request->user();
        $action  = $request->action;
        $count   = 0;

        foreach ($tickets as $ticket) {
            // Authorization check per ticket
            if (! $actor->can('update', $ticket)) {
                continue;
            }

            switch ($action) {
                case 'assign':
                    if ($request->filled('assigned_to')) {
                        // IT Staff can only self-assign
                        $assignTo = $actor->hasRole('it_staff')
                            ? $actor->id
                            : $request->assigned_to;
                        $this->ticketService->assign($ticket, $assignTo, $actor);
                        $count++;
                    }
                    break;

                case 'close':
                    if (! $ticket->isClosed()) {
                        $this->ticketService->updateStatus($ticket, 'closed', $actor);
                        $count++;
                    }
                    break;

                case 'resolve':
                    if (! $ticket->isResolved() && ! $ticket->isClosed()) {
                        $this->ticketService->updateStatus($ticket, 'resolved', $actor);
                        $count++;
                    }
                    break;

                case 'priority':
                    if ($actor->hasRole('it_head') && $request->filled('priority_id')) {
                        $ticket->priority_id = $request->priority_id;
                        $ticket->save();
                        TicketActivity::create([
                            'ticket_id'   => $ticket->id,
                            'user_id'     => $actor->id,
                            'action'      => 'priority_changed',
                            'new_value'   => Priority::find($request->priority_id)?->name,
                            'description' => $actor->name . ' changed priority (bulk action)',
                        ]);
                        $count++;
                    }
                    break;
            }
        }

        $label = match($action) {
            'assign'   => "assigned",
            'close'    => "closed",
            'resolve'  => "resolved",
            'priority' => "priority updated on",
            default    => "updated",
        };

        return back()->with('success', "{$count} ticket(s) {$label} successfully.");
    }
}
