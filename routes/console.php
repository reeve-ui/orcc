<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('orcc4church:info', function (): void {
    $this->info('ORCC4CHURCH — '.config('church.name'));
    $this->line('Impor Excel: php artisan orccdata:import');
});
