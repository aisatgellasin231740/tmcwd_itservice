<?php

namespace App\Http\Controllers\Requester;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user   = $request->user();
        $filter = $request->query('filter', 'all'); // all | open | resolved | closed

        // ── Current counts ─────────────────────────────────────────────────
        $stats = [
            'total'    => $user->tickets()->count(),
            'open'     => $user->tickets()->whereIn('status', ['open', 'in_progress', 'on_hold'])->count(),
            'resolved' => $user->tickets()->where('status', 'resolved')->count(),
            'closed'   => $user->tickets()->where('status', 'closed')->count(),
        ];

        // ── Trend: counts as they stood 7 days ago ─────────────────────────
        // "As of 7 days ago" = tickets that existed at that point in time
        // (created_at <= cutoff) with the status they currently have, which is
        // the best approximation without a full audit-log history.
        $cutoff = Carbon::now()->subDays(7);

        $statsWeekAgo = [
            'total'    => $user->tickets()->where('created_at', '<=', $cutoff)->count(),
            'open'     => $user->tickets()->whereIn('status', ['open', 'in_progress', 'on_hold'])
                                          ->where('created_at', '<=', $cutoff)->count(),
            'resolved' => $user->tickets()->where('status', 'resolved')
                                          ->where('created_at', '<=', $cutoff)->count(),
            'closed'   => $user->tickets()->where('status', 'closed')
                                          ->where('created_at', '<=', $cutoff)->count(),
        ];

        // Build a trend array: null = no history yet; otherwise numeric delta
        $trends = [];
        foreach (['total', 'open', 'resolved', 'closed'] as $key) {
            // If nothing existed a week ago we suppress the trend line entirely
            if ($statsWeekAgo[$key] === 0 && $stats[$key] === 0) {
                $trends[$key] = null;
            } else {
                $trends[$key] = $stats[$key] - $statsWeekAgo[$key];
            }
        }

        // ── Recent tickets (filtered) ──────────────────────────────────────
        $ticketsQuery = $user->tickets()->with(['category', 'priority', 'department']);

        $ticketsQuery = match ($filter) {
            'open'     => $ticketsQuery->whereIn('status', ['open', 'in_progress', 'on_hold']),
            'resolved' => $ticketsQuery->where('status', 'resolved'),
            'closed'   => $ticketsQuery->where('status', 'closed'),
            default    => $ticketsQuery,
        };

        $recentTickets = $ticketsQuery->orderByDesc('created_at')->limit(10)->get();

        return view('requester.dashboard', compact('stats', 'trends', 'recentTickets', 'filter'));
    }
}
