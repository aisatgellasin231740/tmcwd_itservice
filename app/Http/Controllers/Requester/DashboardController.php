<?php

namespace App\Http\Controllers\Requester;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $stats = [
            'total'    => $user->tickets()->count(),
            'open'     => $user->tickets()->whereIn('status', ['open', 'in_progress', 'on_hold'])->count(),
            'resolved' => $user->tickets()->where('status', 'resolved')->count(),
            'closed'   => $user->tickets()->where('status', 'closed')->count(),
        ];

        $recentTickets = $user->tickets()
            ->with(['category', 'priority', 'department'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('requester.dashboard', compact('stats', 'recentTickets'));
    }
}
