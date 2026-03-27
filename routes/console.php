<?php

use App\Actions\Licensing\PruneLicenseHeartbeatsAction;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Command\Command;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('licensing:prune-heartbeats', function (PruneLicenseHeartbeatsAction $pruneHeartbeats): int {
    $deletedCount = $pruneHeartbeats->execute();

    $this->info("Pruned {$deletedCount} expired heartbeat records.");

    return Command::SUCCESS;
})->purpose('Prune license heartbeat records older than the configured retention window');
