<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionController extends Controller
{
    public function destroy(Request $request, AttendanceService $attendance)
    {
        $student = $request->user();

        if ($student) {
            $attendance->exit($student);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('scan')
            ->with('success', 'You have been logged out. Your exit time has been recorded.');
    }
}
