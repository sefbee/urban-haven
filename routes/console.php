<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('uh:release-reservations')->hourly()->withoutOverlapping()->onOneServer();
Schedule::command('uh:overdue-digest')->dailyAt('08:30')->timezone(config('urbanhaven.display_timezone'))->onOneServer();
Schedule::command('uh:prune-leads')->weeklyOn(0, '03:00')->timezone(config('urbanhaven.display_timezone'))->onOneServer();
Schedule::command('uh:prune-exports')->hourly();
Schedule::command('queue:prune-failed --hours=720')->daily();
Schedule::command('auth:clear-resets')->everyFifteenMinutes();
