<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('import:cdc')->dailyAt('05:00');