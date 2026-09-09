<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()
            ->notifications()
            ->orderByDesc('created_at')
            ->paginate(20);

        $request->user()->unreadNotifications->markAsRead();

        return view('notifications.index', compact('notifications'));
    }

    public function markRead(Request $request, string $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        // Derive a redirect URL from the notification payload if possible.
        // Notifications store ticket_id but no url key, so we resolve by role.
        $data     = $notification->data;
        $ticketId = $data['ticket_id'] ?? null;
        $redirect = null;

        if ($ticketId) {
            $user = $request->user();
            try {
                if ($user->hasRole('it_head')) {
                    $redirect = route('admin.tickets.show', $ticketId);
                } elseif ($user->hasRole('it_staff')) {
                    $redirect = route('agent.tickets.show', $ticketId);
                } else {
                    $redirect = route('requester.tickets.show', $ticketId);
                }
            } catch (\Exception) {
                $redirect = null;
            }
        }

        return $redirect ? redirect($redirect) : back();
    }

    public function markAllRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'All notifications marked as read.');
    }
}
