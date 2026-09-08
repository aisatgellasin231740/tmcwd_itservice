<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketActivity;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    /**
     * View the full activity log across all tickets (with filters).
     */
    public function index(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->toDateString());
        $dateTo   = $request->get('date_to',   now()->toDateString());
        $ticketId = $request->get('ticket_id');

        $query = TicketActivity::with(['ticket', 'user'])
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->orderByDesc('created_at');

        if ($ticketId) {
            $query->where('ticket_id', $ticketId);
        }

        $activities = $query->paginate(50)->withQueryString();

        // For the ticket search dropdown
        $tickets = Ticket::orderByDesc('created_at')
            ->select('id', 'ticket_number', 'title')
            ->limit(200)
            ->get();

        return view('admin.activity-log.index', compact(
            'activities', 'dateFrom', 'dateTo', 'ticketId', 'tickets'
        ));
    }

    /**
     * Export activity log as CSV.
     */
    public function exportCsv(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->toDateString());
        $dateTo   = $request->get('date_to',   now()->toDateString());
        $ticketId = $request->get('ticket_id');

        $query = TicketActivity::with(['ticket', 'user'])
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->orderByDesc('created_at');

        if ($ticketId) {
            $query->where('ticket_id', $ticketId);
        }

        $activities = $query->get();
        $filename   = 'tmcwd-activity-log-' . $dateFrom . '-to-' . $dateTo . '.csv';

        return response()->streamDownload(function () use ($activities) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Ticket #', 'Ticket Title', 'Action', 'Description',
                'Old Value', 'New Value', 'Performed By', 'Date & Time',
            ]);
            foreach ($activities as $act) {
                fputcsv($handle, [
                    $act->ticket?->ticket_number ?? '—',
                    $act->ticket?->title ?? '—',
                    $act->action,
                    $act->description ?? $act->description_label,
                    $act->old_value ?? '—',
                    $act->new_value ?? '—',
                    $act->user?->name ?? 'System',
                    $act->created_at->format('Y-m-d H:i:s'),
                ]);
            }
            fclose($handle);
        }, $filename, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Export activity log as PDF.
     */
    public function exportPdf(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->toDateString());
        $dateTo   = $request->get('date_to',   now()->toDateString());
        $ticketId = $request->get('ticket_id');

        $query = TicketActivity::with(['ticket', 'user'])
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->orderByDesc('created_at');

        if ($ticketId) {
            $query->where('ticket_id', $ticketId);
        }

        $activities = $query->limit(500)->get(); // cap at 500 for PDF size

        $pdf = Pdf::loadView('admin.activity-log.pdf', compact(
            'activities', 'dateFrom', 'dateTo'
        ))->setPaper('a4', 'landscape');

        return $pdf->download('tmcwd-activity-log.pdf');
    }
}
