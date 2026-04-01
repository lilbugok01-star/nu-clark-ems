<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('events:send-reminders')->dailyAt('08:00');
