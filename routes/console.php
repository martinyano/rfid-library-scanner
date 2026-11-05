<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('library:send-due-reminders')->dailyAt('08:00');
Schedule::command('library:close-stale-attendance')->dailyAt('23:30');
