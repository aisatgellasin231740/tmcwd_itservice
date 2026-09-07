<?php

namespace App\Http\Controllers\Admin;

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

        if ($request->filled('status'))       { $query->where('status', $request->status); }
        if ($request->filled('priority_id'))  { $query->where('priority_id', $request->priority_id); }
        if ($request->filled('category_id'))  { $query->where('category_id', $request->category_id); }
        if ($request->filled('department_id')){ $query->where('department_id', $request->department_id); }
        if ($request->filled('assigned_to'))  { $query->where('assigned_to', $request->assigned_to); }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('title', 'like', "%{$s}%")
                                      ->orWhere('ticket_number', 'like', "%{$s}%"));
        }

        $sortField = in_array($request->get('sort'), ['created_at','sla_due_at','status','priority_id'])
            ? $request->get('sort') : 'created_at';
        $query->orderBy($sortField, $request->get('dir', 'desc') === 'asc' ? 'asc' : 'desc');

        $tickets     = $query->paginate(20)->withQueryString();
        $categories  = Category::active()->orderBy('name')->get();
        $priorities  = Priority::ordered()->get();
        $departments = Department::active()->orderBy('name')->get();
        $agents      = User::role(['it_staff', 'it_head'])->where('is_active', true)->orderBy('name')->get();

        return view('admin.tickets.index', compact(
            'tickets', 'categories', 'priorities', 'departments', 'agents'
        ));
    }

    public function show(Request $request, Ticket $ticket)
    {
        $ticket->load([
            'category', 'priority', 'department',
            'requester', 'assignee',
            'attachments.user',
            'activities.user',
            'comments.user',
        ]);

        $agents      = User::role(['it_staff', 'it_head'])->where('is_active', true)->orderBy('name')->get();
        $priorities  = Priority::ordered()->get();

        return view('admin.tickets.show', compact('ticket', 'agents', 'priorities'));
    }

    public function update(UpdateTicketRequest $request, Ticket $ticket)
    {
        $data = $request->validated();

        if (isset($data['status'])) {
            $this->ticketService->updateStatus($ticket, $data['status'], $request->user());
        }
        if (array_key_exists('assigned_to', $data)) {
            $this->ticketService->assign($ticket, $data['assigned_to'], $request->user());
        }
        if (isset($data['priority_id'])) {
            $ticket->priority_id = $data['priority_id'];
            $ticket->save();
        }

        return back()->with('success', 'Ticket updated.');
    }

    public function destroy(Ticket $ticket)
    {
        $this->authorize('delete', $ticket);
        $number = $ticket->ticket_number;
        $ticket->delete();

        return redirect()->route('admin.tickets.index')
            ->with('success', "Ticket {$number} has been deleted.");
    }

    public function storeComment(StoreCommentRequest $request, Ticket $ticket)
    {
        $isInternal = (bool) $request->input('is_internal', false);

        $this->ticketService->addComment(
            $ticket,
            $request->user(),
            $request->validated('body'),
            $isInternal
        );

        return back()->with('success', $isInternal ? 'Internal note added.' : 'Reply sent.');
    }
}
