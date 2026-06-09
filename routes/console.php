<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('vpn:check-expired')->hourly();
Schedule::command('invoice:check-overdue')->dailyAt('00:00');
