<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $query = trim($request->get('q', ''));

        if (strlen($query) < 2) {
            return view('search.results', [
                'tickets' => collect(),
                'query'   => $query,
                'tooShort' => true,
            ]);
        }

        $user      = $request->user();
        $isAdmin   = $user->hasRole('it_head');
        $isAgent   = $user->hasRole('it_staff');

        $ticketQuery = Ticket::with(['category', 'priority', 'department', 'requester', 'assignee'])
            ->where(function ($q) use ($query) {
                $q->where('ticket_number', 'like', "%{$query}%")
                  ->orWhere('title', 'like', "%{$query}%")
                  ->orWhereHas('requester', fn($r) => $r->where('name', 'like', "%{$query}%"));
            });

        // Scope by role
        if ($isAdmin) {
            // Admin sees all tickets — no additional scope
        } elseif ($isAgent) {
            // Agent sees all tickets (can be assigned any ticket)
            // but not requesters' private data beyond their scope
        } else {
            // Requester only sees their own tickets
            $ticketQuery->where('requester_id', $user->id);
        }

        $tickets = $ticketQuery
            ->orderByDesc('created_at')
            ->limit(30)
            ->get();

        return view('search.results', compact('tickets', 'query'));
    }
}
