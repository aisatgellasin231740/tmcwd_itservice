<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Department;
use App\Models\Ticket;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->toDateString());
        $dateTo   = $request->get('date_to',   now()->toDateString());

        // Volume by department
        $byDepartment = Department::select('departments.name')
            ->selectRaw('COUNT(tickets.id) as ticket_count')
            ->leftJoin('tickets', function ($join) use ($dateFrom, $dateTo) {
                $join->on('tickets.department_id', '=', 'departments.id')
                     ->whereBetween('tickets.created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);
            })
            ->groupBy('departments.id', 'departments.name')
            ->orderByDesc('ticket_count')
            ->get();

        // Avg resolution time by category (hours)
        $byCategory = Category::select('categories.name')
            ->selectRaw('ROUND(AVG(TIMESTAMPDIFF(MINUTE, tickets.created_at, tickets.resolved_at)) / 60, 1) as avg_hours')
            ->selectRaw('COUNT(tickets.id) as resolved_count')
            ->leftJoin('tickets', function ($join) use ($dateFrom, $dateTo) {
                $join->on('tickets.category_id', '=', 'categories.id')
                     ->whereNotNull('tickets.resolved_at')
                     ->whereBetween('tickets.created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);
            })
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('resolved_count')
            ->get();

        // Summary stats
        $summary = [
            'total'      => Ticket::whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])->count(),
            'open'       => Ticket::whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])->where('status', 'open')->count(),
            'resolved'   => Ticket::whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])->where('status', 'resolved')->count(),
            'closed'     => Ticket::whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])->where('status', 'closed')->count(),
            'sla_breach' => Ticket::whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
                                  ->whereNotIn('status', ['resolved','closed'])
                                  ->where('sla_due_at', '<', now())
                                  ->count(),
            'avg_resolution' => round(
                Ticket::whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
                      ->whereNotNull('resolved_at')
                      ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) as avg')
                      ->value('avg') ?? 0, 1
            ),
        ];

        return view('admin.reports.index', compact(
            'byDepartment', 'byCategory', 'summary', 'dateFrom', 'dateTo'
        ));
    }

    public function exportCsv(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->toDateString());
        $dateTo   = $request->get('date_to',   now()->toDateString());

        $tickets = Ticket::with(['department', 'category', 'priority', 'requester', 'assignee'])
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->orderByDesc('created_at')
            ->get();

        $filename = 'tmcwd-it-report-' . $dateFrom . '-to-' . $dateTo . '.csv';

        return response()->streamDownload(function () use ($tickets) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Ticket Number', 'Title', 'Requester', 'Department',
                'Category', 'Priority', 'Status', 'Assigned To',
                'Created At', 'Resolved At', 'SLA Due At',
            ]);

            foreach ($tickets as $ticket) {
                fputcsv($handle, [
                    $ticket->ticket_number,
                    $ticket->title,
                    $ticket->requester?->name,
                    $ticket->department?->name,
                    $ticket->category?->name,
                    $ticket->priority?->name,
                    $ticket->statusLabel(),
                    $ticket->assignee?->name ?? 'Unassigned',
                    $ticket->created_at?->format('Y-m-d H:i'),
                    $ticket->resolved_at?->format('Y-m-d H:i') ?? '',
                    $ticket->sla_due_at?->format('Y-m-d H:i') ?? '',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function exportPdf(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->toDateString());
        $dateTo   = $request->get('date_to',   now()->toDateString());

        $tickets = Ticket::with(['department', 'category', 'priority', 'requester', 'assignee'])
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->orderByDesc('created_at')
            ->get();

        $summary = [
            'total'          => $tickets->count(),
            'open'           => $tickets->whereIn('status', ['open', 'in_progress', 'on_hold'])->count(),
            'resolved'       => $tickets->where('status', 'resolved')->count(),
            'closed'         => $tickets->where('status', 'closed')->count(),
            'sla_breach'     => $tickets->filter(fn($t) =>
                                    ! $t->isFinished()
                                    && $t->sla_due_at
                                    && now()->gt($t->sla_due_at)
                                )->count(),
            'avg_resolution' => round(
                $tickets->whereNotNull('resolved_at')
                        ->avg(fn($t) => $t->created_at->diffInHours($t->resolved_at)) ?? 0, 1
            ),
        ];

        $pdf = Pdf::loadView('admin.reports.pdf', compact(
            'tickets', 'summary', 'dateFrom', 'dateTo'
        ))->setPaper('a4', 'landscape');

        return $pdf->download('tmcwd-it-performance-report.pdf');
    }
}
