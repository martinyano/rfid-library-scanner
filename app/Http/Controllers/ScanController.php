<?php

namespace App\Http\Controllers;

use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScanController extends Controller
{
    public function __construct(private readonly AttendanceService $attendance) {}

    public function show(Request $request)
    {
        if ($request->user()?->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        if ($request->user()?->isStudent()) {
            return redirect()->route('student.dashboard');
        }

        return view('scan');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'rfid_uid' => ['required', 'string', 'max:100'],
        ]);

        $student = $this->attendance->findStudentByRfid($validated['rfid_uid']);

        if (! $student) {
            return back()
                ->withInput()
                ->with('error', 'This RFID card is not registered. Please contact the librarian.');
        }

        if (! $student->is_active) {
            return back()
                ->withInput()
                ->with('error', 'This student account is inactive. Please contact the librarian.');
        }

        $currentUser = $request->user();

        if ($currentUser?->isAdmin()) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Please use a separate browser session to scan student cards.');
        }

        if ($currentUser?->isStudent() && $currentUser->id !== $student->id) {
            $this->attendance->exit($currentUser);
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        $this->attendance->entry(
            $student,
            'scan_page',
            $request->ip(),
            $request->userAgent()
        );

        Auth::login($student);
        $request->session()->regenerate();

        return redirect()->route('student.dashboard')
            ->with('success', 'Welcome back, '.$student->name.'. Your library visit has been logged.');
    }
}
