<?php

namespace App\Http\Controllers\Requester;

use App\Http\Controllers\Controller;
use App\Http\Requests\RequesterUpdateTicketRequest;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\StoreTicketRequest;
use App\Models\Category;
use App\Models\Department;
use App\Models\Priority;
use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Services\TicketService;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function __construct(private readonly TicketService $ticketService)
    {
    }

    public function index(Request $request)
    {
        $query = $request->user()->tickets()
            ->with(['category', 'priority', 'department']);

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $tickets = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        return view('requester.tickets.index', compact('tickets'));
    }

    public function create(Request $request)
    {
        $departments = Department::active()->orderBy('name')->get();
        $categories  = Category::active()->orderBy('name')->get();
        $priorities  = Priority::ordered()->get();

        return view('requester.tickets.create', compact('departments', 'categories', 'priorities'));
    }

    public function store(StoreTicketRequest $request)
    {
        $ticket = $this->ticketService->create($request->validated(), $request->user());

        if ($request->hasFile('attachments')) {
            $this->ticketService->storeAttachments($ticket, $request->file('attachments'), $request->user());
        }

        return redirect()
            ->route('requester.tickets.show', $ticket)
            ->with('success', "Ticket {$ticket->ticket_number} submitted successfully.");
    }

    public function show(Request $request, Ticket $ticket)
    {
        $this->authorize('view', $ticket);

        $ticket->load([
            'category', 'priority', 'department',
            'requester', 'assignee',
            'attachments',
            'activities.user',
        ]);

        // Requesters only see public comments (with their attachments)
        $comments = $ticket->publicComments()->with(['user', 'attachments'])->get();

        return view('requester.tickets.show', compact('ticket', 'comments'));
    }

    public function storeComment(StoreCommentRequest $request, Ticket $ticket)
    {
        $this->authorize('addComment', $ticket);

        $this->ticketService->addComment(
            $ticket,
            $request->user(),
            $request->validated('body'),
            false, // always public for requesters
            $request->hasFile('attachments') ? $request->file('attachments') : []
        );

        return back()->with('success', 'Reply added.');
    }

    // ── Requester edit (open tickets only) ─────────────────────────────────

    public function edit(Request $request, Ticket $ticket)
    {
        $this->authorize('editOwn', $ticket);

        $categories = Category::active()->orderBy('name')->get();

        return view('requester.tickets.edit', compact('ticket', 'categories'));
    }

    public function update(RequesterUpdateTicketRequest $request, Ticket $ticket)
    {
        $this->authorize('editOwn', $ticket);

        $old = $ticket->only(['title', 'description', 'category_id']);

        $ticket->update($request->validated());
        $ticket->refresh();

        // Log each changed field
        $changed = [];
        if ($old['title'] !== $ticket->title)               $changed[] = 'title';
        if ($old['description'] !== $ticket->description)   $changed[] = 'description';
        if ($old['category_id'] !== $ticket->category_id)   $changed[] = 'category';

        if ($changed) {
            TicketActivity::create([
                'ticket_id'   => $ticket->id,
                'user_id'     => $request->user()->id,
                'action'      => 'edited',
                'description' => $request->user()->name . ' edited ticket (' . implode(', ', $changed) . ')',
            ]);
        }

        return redirect()
            ->route('requester.tickets.show', $ticket)
            ->with('success', 'Ticket updated successfully.');
    }

    // ── Requester cancel/withdraw (open tickets only) ──────────────────────

    public function cancel(Request $request, Ticket $ticket)
    {
        $this->authorize('cancel', $ticket);

        $this->ticketService->updateStatus($ticket, 'closed', $request->user());

        // Override the activity description to say "withdrawn" instead of status change
        TicketActivity::where('ticket_id', $ticket->id)
            ->where('action', 'status_changed')
            ->latest()
            ->first()
            ?->update(['description' => $request->user()->name . ' withdrew (cancelled) this ticket.']);

        return redirect()
            ->route('requester.tickets.index')
            ->with('success', "Ticket {$ticket->ticket_number} has been withdrawn.");
    }

    // ── Requester explicit reopen (resolved tickets only) ──────────────────

    public function reopen(Request $request, Ticket $ticket)
    {
        $this->authorize('reopen', $ticket);

        $request->validate([
            'reopen_reason' => ['required', 'string', 'min:10', 'max:1000'],
        ], [
            'reopen_reason.required' => 'Please explain why this ticket was not resolved.',
            'reopen_reason.min'      => 'Please provide at least 10 characters.',
        ]);

        $this->ticketService->reopen($ticket, $request->user(), $request->input('reopen_reason'));

        return back()->with('success', 'Ticket has been reopened. The IT team will follow up.');
    }
}
