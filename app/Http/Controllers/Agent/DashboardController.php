<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $today = now()->startOfDay();

        $stats = [
            'assigned_open'    => Ticket::assignedTo($user->id)
                                        ->whereNotIn('status', ['resolved', 'closed'])
                                        ->count(),
            'in_progress'      => Ticket::assignedTo($user->id)
                                        ->where('status', 'in_progress')
                                        ->count(),
            'resolved_today'   => Ticket::assignedTo($user->id)
                                        ->where('status', 'resolved')
                                        ->whereDate('resolved_at', $today)
                                        ->count(),
            'sla_breached'     => Ticket::assignedTo($user->id)
                                        ->whereNotIn('status', ['resolved', 'closed'])
                                        ->whereNotNull('sla_due_at')
                                        ->where('sla_due_at', '<', now())
                                        ->count(),
            'unassigned_open'  => Ticket::unassigned()
                                        ->where('status', 'open')
                                        ->count(),
        ];

        // Unassigned queue — newest first, limit 10
        $unassignedQueue = Ticket::unassigned()
            ->where('status', 'open')
            ->with(['category', 'priority', 'department', 'requester'])
            ->orderByRaw("FIELD(priority_id, (SELECT id FROM priorities WHERE name='Urgent'), (SELECT id FROM priorities WHERE name='High'), (SELECT id FROM priorities WHERE name='Medium'), (SELECT id FROM priorities WHERE name='Low'))")
            ->limit(10)
            ->get();

        // My active tickets — most urgent first
        $myTickets = Ticket::assignedTo($user->id)
            ->whereNotIn('status', ['resolved', 'closed'])
            ->with(['category', 'priority', 'department', 'requester'])
            ->orderBy('sla_due_at')
            ->limit(10)
            ->get();

        return view('agent.dashboard', compact('stats', 'unassignedQueue', 'myTickets'));
    }
}
