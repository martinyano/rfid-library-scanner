<?php

namespace App\Services;

use App\Models\AttendanceLog;
use App\Models\User;

class AttendanceService
{
    public function normalizeUid(?string $uid): string
    {
        return strtoupper(preg_replace('/\s+/', '', trim((string) $uid)));
    }

    public function findStudentByRfid(?string $uid): ?User
    {
        $normalized = $this->normalizeUid($uid);

        if ($normalized === '') {
            return null;
        }

        return User::query()
            ->students()
            ->where('rfid_uid', $normalized)
            ->first();
    }

    public function openFor(User $student): ?AttendanceLog
    {
        return AttendanceLog::query()
            ->where('user_id', $student->id)
            ->whereNull('exit_at')
            ->latest('entry_at')
            ->first();
    }

    public function entry(User $student, string $source = 'scan_page', ?string $ip = null, ?string $userAgent = null): AttendanceLog
    {
        $open = $this->openFor($student);

        if ($open) {
            return $open;
        }

        return AttendanceLog::query()->create([
            'user_id' => $student->id,
            'rfid_uid' => $student->rfid_uid,
            'entry_at' => now(),
            'source' => $source,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
        ]);
    }

    public function exit(User $student): ?AttendanceLog
    {
        $open = $this->openFor($student);

        if ($open) {
            $open->update(['exit_at' => now()]);
        }

        return $open;
    }

    /**
     * @return array{action: string, log: AttendanceLog}
     */
    public function toggle(User $student, string $source, ?string $ip = null, ?string $userAgent = null): array
    {
        $open = $this->openFor($student);

        if ($open) {
            $open->update(['exit_at' => now()]);

            return ['action' => 'exit', 'log' => $open->refresh()];
        }

        return [
            'action' => 'entry',
            'log' => $this->entry($student, $source, $ip, $userAgent),
        ];
    }

    public function closeStale(int $hours = 12): int
    {
        $threshold = now()->subHours($hours);

        $logs = AttendanceLog::query()
            ->whereNull('exit_at')
            ->where('entry_at', '<', $threshold)
            ->get();

        foreach ($logs as $log) {
            $log->update([
                'exit_at' => $log->entry_at->copy()->addHours($hours),
                'closed_automatically' => true,
            ]);
        }

        return $logs->count();
    }
}
