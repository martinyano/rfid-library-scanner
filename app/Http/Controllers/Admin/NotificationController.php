<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.notifications.index', [
            'notifications' => $request->user()->notifications()->paginate(15),
        ]);
    }

    public function markRead(Request $request, string $notification)
    {
        $request->user()->unreadNotifications()
            ->where('id', $notification)
            ->firstOrFail()
            ->markAsRead();

        return back()->with('success', 'Notification marked as read.');
    }

    public function readAll(Request $request)
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return back()->with('success', 'All notifications marked as read.');
    }
}
