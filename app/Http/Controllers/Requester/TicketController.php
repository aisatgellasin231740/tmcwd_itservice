<?php

namespace App\Http\Controllers\Requester;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\StoreTicketRequest;
use App\Models\Category;
use App\Models\Department;
use App\Models\Priority;
use App\Models\Ticket;
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

        // Requesters only see public comments
        $comments = $ticket->publicComments()->with('user')->get();

        return view('requester.tickets.show', compact('ticket', 'comments'));
    }

    public function storeComment(StoreCommentRequest $request, Ticket $ticket)
    {
        $this->authorize('addComment', $ticket);

        $this->ticketService->addComment(
            $ticket,
            $request->user(),
            $request->validated('body'),
            false // always public for requesters
        );

        return back()->with('success', 'Reply added.');
    }
}
