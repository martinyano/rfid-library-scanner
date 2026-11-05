<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RfidScanController extends Controller
{
    public function __invoke(Request $request, AttendanceService $attendance): JsonResponse
    {
        $validated = $request->validate([
            'rfid_uid' => ['required', 'string', 'max:100'],
        ]);

        $student = $attendance->findStudentByRfid($validated['rfid_uid']);

        if (! $student) {
            return response()->json([
                'status' => 'error',
                'message' => 'RFID card is not registered.',
            ], 404);
        }

        if (! $student->is_active) {
            return response()->json([
                'status' => 'error',
                'message' => 'Student account is inactive.',
            ], 403);
        }

        $result = $attendance->toggle(
            $student,
            'api',
            $request->ip(),
            $request->userAgent()
        );

        return response()->json([
            'status' => 'ok',
            'action' => $result['action'],
            'message' => $result['action'] === 'entry'
                ? 'Entry recorded for '.$student->name.'.'
                : 'Exit recorded for '.$student->name.'.',
            'student' => [
                'name' => $student->name,
                'student_number' => $student->student_number,
                'course' => $student->course,
            ],
            'logged_at' => ($result['action'] === 'entry'
                ? $result['log']->entry_at
                : $result['log']->exit_at)?->toIso8601String(),
            'dashboard_url' => route('scan'),
        ]);
    }
}
