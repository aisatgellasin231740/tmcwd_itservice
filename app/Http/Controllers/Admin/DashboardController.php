<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Department;
use App\Models\Ticket;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $now = now();

        // ── Date range — stored in URL so it is bookmarkable & refresh-safe ──
        $period   = $request->get('period', 'month'); // this_week | month | quarter | year | custom
        $dateFrom = null;
        $dateTo   = null;

        switch ($period) {
            case 'this_week':
                $dateFrom = $now->copy()->startOfWeek()->toDateString();
                $dateTo   = $now->copy()->endOfWeek()->toDateString();
                break;
            case 'quarter':
                $dateFrom = $now->copy()->firstOfQuarter()->toDateString();
                $dateTo   = $now->copy()->toDateString();
                break;
            case 'year':
                $dateFrom = $now->copy()->startOfYear()->toDateString();
                $dateTo   = $now->copy()->toDateString();
                break;
            case 'custom':
                $dateFrom = $request->get('date_from', $now->copy()->startOfMonth()->toDateString());
                $dateTo   = $request->get('date_to',   $now->toDateString());
                break;
            default: // month
                $period   = 'month';
                $dateFrom = $now->copy()->startOfMonth()->toDateString();
                $dateTo   = $now->toDateString();
        }

        $rangeStart = $dateFrom . ' 00:00:00';
        $rangeEnd   = $dateTo   . ' 23:59:59';

        // ── Summary stats (range-aware) ──────────────────────────────────────
        $stats = [
            'total_open'     => Ticket::whereNotIn('status', ['resolved', 'closed'])->count(),
            'resolved_range' => Ticket::where('status', 'resolved')
                                      ->whereBetween('resolved_at', [$rangeStart, $rangeEnd])
                                      ->count(),
            'closed_range'   => Ticket::where('status', 'closed')
                                      ->whereBetween('closed_at', [$rangeStart, $rangeEnd])
                                      ->count(),
            'sla_breached'   => Ticket::whereNotIn('status', ['resolved', 'closed'])
                                      ->whereNotNull('sla_due_at')
                                      ->where('sla_due_at', '<', now())
                                      ->count(),
            'avg_resolution' => round(
                Ticket::whereNotNull('resolved_at')
                      ->whereBetween('created_at', [$rangeStart, $rangeEnd])
                      ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) as avg_hours')
                      ->value('avg_hours') ?? 0,
                1
            ),
        ];

        // ── Charts — filtered by date range ─────────────────────────────────
        $byDepartment = Department::select('departments.id', 'departments.name')
            ->selectRaw('COUNT(tickets.id) as tickets_count')
            ->leftJoin('tickets', function ($join) use ($rangeStart, $rangeEnd) {
                $join->on('tickets.department_id', '=', 'departments.id')
                     ->whereBetween('tickets.created_at', [$rangeStart, $rangeEnd]);
            })
            ->groupBy('departments.id', 'departments.name')
            ->orderByDesc('tickets_count')
            ->get()
            ->map(fn($d) => ['label' => $d->name, 'count' => (int) $d->tickets_count]);

        $byCategory = Category::select('categories.id', 'categories.name')
            ->selectRaw('COUNT(tickets.id) as tickets_count')
            ->leftJoin('tickets', function ($join) use ($rangeStart, $rangeEnd) {
                $join->on('tickets.category_id', '=', 'categories.id')
                     ->whereBetween('tickets.created_at', [$rangeStart, $rangeEnd]);
            })
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('tickets_count')
            ->get();

        // All-time status summary (not date-filtered — shows current live state)
        $byStatus = Ticket::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Recent urgent tickets (all-time, not date-filtered)
        $urgentTickets = Ticket::whereHas('priority', fn($q) => $q->where('name', 'Urgent'))
            ->whereNotIn('status', ['closed'])
            ->with(['category', 'department', 'requester', 'priority', 'assignee'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact(
            'stats', 'byDepartment', 'byCategory', 'byStatus', 'urgentTickets',
            'period', 'dateFrom', 'dateTo'
        ));
    }
}
