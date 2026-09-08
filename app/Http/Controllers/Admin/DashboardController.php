<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Department;
use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $now = now();

        // ── Date range ───────────────────────────────────────────────────────
        $period   = $request->get('period', 'month');
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
            default:
                $period   = 'month';
                $dateFrom = $now->copy()->startOfMonth()->toDateString();
                $dateTo   = $now->toDateString();
        }

        $rangeStart = $dateFrom . ' 00:00:00';
        $rangeEnd   = $dateTo   . ' 23:59:59';

        // ── Previous period for trend comparison ─────────────────────────────
        $days          = \Carbon\Carbon::parse($dateFrom)->diffInDays(\Carbon\Carbon::parse($dateTo)) + 1;
        $prevEnd       = \Carbon\Carbon::parse($dateFrom)->subDay()->endOfDay();
        $prevStart     = $prevEnd->copy()->subDays($days - 1)->startOfDay();
        $prevRangeStart = $prevStart->toDateTimeString();
        $prevRangeEnd   = $prevEnd->toDateTimeString();

        // ── Summary stats ────────────────────────────────────────────────────
        $totalOpen      = Ticket::whereNotIn('status', ['resolved', 'closed'])->count();
        $resolvedRange  = Ticket::where('status', 'resolved')->whereBetween('resolved_at', [$rangeStart, $rangeEnd])->count();
        $closedRange    = Ticket::where('status', 'closed')->whereBetween('closed_at', [$rangeStart, $rangeEnd])->count();
        $slaBreached    = Ticket::whereNotIn('status', ['resolved', 'closed'])->whereNotNull('sla_due_at')->where('sla_due_at', '<', now())->count();
        $unassigned     = Ticket::whereNull('assigned_to')->whereNotIn('status', ['resolved', 'closed'])->count();

        $avgResolution  = round(
            Ticket::whereNotNull('resolved_at')
                  ->whereBetween('created_at', [$rangeStart, $rangeEnd])
                  ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) as avg_hours')
                  ->value('avg_hours') ?? 0, 1
        );

        // Total tickets in period for SLA health
        $totalPeriod   = Ticket::whereBetween('created_at', [$rangeStart, $rangeEnd])->count();
        $resolvedOnTime = Ticket::whereNotNull('resolved_at')
            ->whereBetween('created_at', [$rangeStart, $rangeEnd])
            ->whereColumn('resolved_at', '<=', 'sla_due_at')
            ->count();
        $slaHealthPct = $totalPeriod > 0 ? round(($resolvedOnTime / max($totalPeriod, 1)) * 100) : 100;

        // Trends vs previous period
        $prevResolved = Ticket::where('status', 'resolved')->whereBetween('resolved_at', [$prevRangeStart, $prevRangeEnd])->count();
        $prevOpen     = Ticket::whereNotIn('status', ['resolved', 'closed'])->where('created_at', '<', $rangeStart)->count();

        $stats = [
            'total_open'      => $totalOpen,
            'resolved_range'  => $resolvedRange,
            'closed_range'    => $closedRange,
            'sla_breached'    => $slaBreached,
            'avg_resolution'  => $avgResolution,
            'unassigned'      => $unassigned,
            'sla_health_pct'  => $slaHealthPct,
            'resolved_trend'  => $resolvedRange - $prevResolved, // positive = improved
        ];

        // ── Charts ──────────────────────────────────────────────────────────
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

        $byStatus = Ticket::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // ── IT Staff performance ─────────────────────────────────────────────
        $staffPerformance = User::role(['it_staff', 'it_head'])
            ->where('is_active', true)
            ->withCount([
                'assignedTickets as assigned_total'   => fn($q) => $q->whereNotIn('status', ['closed']),
                'assignedTickets as resolved_today'   => fn($q) => $q->where('status', 'resolved')->whereDate('resolved_at', today()),
                'assignedTickets as sla_breaches'     => fn($q) => $q->whereNotIn('status', ['resolved','closed'])->whereNotNull('sla_due_at')->where('sla_due_at', '<', now()),
                'assignedTickets as resolved_period'  => fn($q) => $q->where('status', 'resolved')->whereBetween('resolved_at', [$rangeStart, $rangeEnd]),
            ])
            ->orderByDesc('resolved_period')
            ->get();

        // ── Recent activity feed (last 12 actions across all tickets) ────────
        $recentActivity = TicketActivity::with(['ticket', 'user'])
            ->whereNotNull('user_id')
            ->orderByDesc('created_at')
            ->limit(12)
            ->get();

        // ── Urgent tickets ───────────────────────────────────────────────────
        $urgentTickets = Ticket::whereHas('priority', fn($q) => $q->where('name', 'Urgent'))
            ->whereNotIn('status', ['closed'])
            ->with(['category', 'department', 'requester', 'priority', 'assignee'])
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        return view('admin.dashboard', compact(
            'stats', 'byDepartment', 'byCategory', 'byStatus',
            'urgentTickets', 'staffPerformance', 'recentActivity',
            'period', 'dateFrom', 'dateTo'
        ));
    }
}
