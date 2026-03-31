<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\SyncWorkspaceResourcesJob;
use App\Jobs\ScanIdleResourcesJob;
use App\Jobs\SyncCostDataJob;
use App\Jobs\RunScheduledActionJob;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// CloudSaviour scheduled jobs
Schedule::job(new SyncWorkspaceResourcesJob)->hourly();
Schedule::job(new ScanIdleResourcesJob)->hourly();
Schedule::job(new SyncCostDataJob)->dailyAt('01:00');
Schedule::job(new RunScheduledActionJob)->everyMinute();
