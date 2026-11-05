<?php

namespace App\Console\Commands;

use App\Services\AttendanceService;
use App\Services\SettingService;
use Illuminate\Console\Command;

class CloseStaleAttendance extends Command
{
    protected $signature = 'library:close-stale-attendance';

    protected $description = 'Close attendance sessions that were left open for too long';

    public function handle(SettingService $settings, AttendanceService $attendance): int
    {
        $hours = $settings->int('auto_close_hours', 12);
        $count = $attendance->closeStale($hours);

        $this->info("Closed {$count} stale attendance session(s) after {$hours} hour(s).");

        return self::SUCCESS;
    }
}
