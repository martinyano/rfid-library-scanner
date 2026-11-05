<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->input('date', today()->toDateString());

        $logs = AttendanceLog::query()
            ->with('user')
            ->when($date !== 'all' && $date, fn ($query) => $query->whereDate('entry_at', $date))
            ->when($request->input('filter') === 'open', fn ($query) => $query->whereNull('exit_at'))
            ->when($request->string('q')->trim()->toString(), function ($query, $q) {
                $query->whereHas('user', function ($query) use ($q) {
                    $query->where('name', 'like', "%{$q}%")
                        ->orWhere('student_number', 'like', "%{$q}%")
                        ->orWhere('rfid_uid', 'like', "%{$q}%");
                });
            })
            ->latest('entry_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.attendance.index', [
            'logs' => $logs,
            'date' => $date,
            'insideNow' => AttendanceLog::query()->open()->count(),
            'todayEntries' => AttendanceLog::query()->whereDate('entry_at', today())->count(),
            'todayExits' => AttendanceLog::query()->whereDate('exit_at', today())->count(),
        ]);
    }

    public function close(Request $request, AttendanceLog $log)
    {
        if ($log->exit_at !== null) {
            return back()->with('error', 'This attendance session is already closed.');
        }

        $log->update([
            'exit_at' => now(),
            'closed_automatically' => false,
        ]);

        return back()->with('success', 'Attendance session closed.');
    }
}
