<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\UpdateTicketRequest;
use App\Models\Category;
use App\Models\Department;
use App\Models\Priority;
use App\Models\Ticket;
use App\Models\User;
use App\Services\TicketService;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function __construct(private readonly TicketService $ticketService)
    {
    }

    public function index(Request $request)
    {
        $query = Ticket::with(['category', 'priority', 'department', 'requester', 'assignee']);

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority_id')) {
            $query->where('priority_id', $request->priority_id);
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        if ($request->filled('assignee')) {
            if ($request->assignee === 'me') {
                $query->assignedTo($request->user()->id);
            } elseif ($request->assignee === 'unassigned') {
                $query->unassigned();
            }
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('ticket_number', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortField = $request->get('sort', 'created_at');
        $sortDir   = $request->get('dir', 'desc');
        $allowed   = ['created_at', 'sla_due_at', 'status', 'priority_id'];
        if (in_array($sortField, $allowed)) {
            $query->orderBy($sortField, $sortDir);
        }

        $tickets     = $query->paginate(20)->withQueryString();
        $categories  = Category::active()->orderBy('name')->get();
        $priorities  = Priority::ordered()->get();
        $departments = Department::active()->orderBy('name')->get();
        $agents      = User::role(['it_staff', 'it_head'])->where('is_active', true)->orderBy('name')->get();

        return view('agent.tickets.index', compact(
            'tickets', 'categories', 'priorities', 'departments', 'agents'
        ));
    }

    public function show(Request $request, Ticket $ticket)
    {
        $this->authorize('view', $ticket);

        $ticket->load([
            'category', 'priority', 'department',
            'requester', 'assignee',
            'attachments.user',
            'activities.user',
            'comments.user',
            'comments.attachments',
        ]);

        $agents = User::role(['it_staff', 'it_head'])->where('is_active', true)->orderBy('name')->get();

        return view('agent.tickets.show', compact('ticket', 'agents'));
    }

    public function update(UpdateTicketRequest $request, Ticket $ticket)
    {
        $this->authorize('update', $ticket);

        $data = $request->validated();

        if (isset($data['status'])) {
            $this->ticketService->updateStatus($ticket, $data['status'], $request->user());
        }

        if (array_key_exists('assigned_to', $data)) {
            // IT Staff can only assign to themselves — IT Head can assign to anyone
            if ($request->user()->hasRole('it_staff') && ! empty($data['assigned_to'])) {
                $data['assigned_to'] = $request->user()->id;
            }
            $this->ticketService->assign($ticket, $data['assigned_to'], $request->user());
        }

        if (isset($data['priority_id'])) {
            $this->authorize('changePriority', $ticket);
            $ticket->priority_id = $data['priority_id'];
            $ticket->save();
        }

        // If the action was triggered from the dashboard, go back there
        if ($request->input('_from') === 'dashboard') {
            return redirect()->route('agent.dashboard')
                ->with('success', 'Ticket assigned to you.');
        }

        return back()->with('success', 'Ticket updated.');
    }

    public function storeComment(StoreCommentRequest $request, Ticket $ticket)
    {
        $this->authorize('addComment', $ticket);

        $isInternal = (bool) $request->input('is_internal', false);

        // Requesters cannot post internal notes
        if ($isInternal && ! $request->user()->hasAnyRole(['it_staff', 'it_head'])) {
            abort(403);
        }

        $this->ticketService->addComment(
            $ticket,
            $request->user(),
            $request->validated('body'),
            $isInternal,
            $request->hasFile('attachments') ? $request->file('attachments') : []
        );

        return back()->with('success', $isInternal ? 'Internal note added.' : 'Reply sent.');
    }
}
