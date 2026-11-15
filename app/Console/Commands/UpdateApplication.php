<?php

namespace App\Console\Commands;

use App\Services\UpdateService;
use Illuminate\Console\Command;

class UpdateApplication extends Command
{
    protected $signature = 'orcc4church:update
                            {--user=0 : ID pengguna yang memulai pembaruan}';

    protected $description = 'Memperbarui aplikasi dari repositori GitHub.';

    public function handle(UpdateService $updates): int
    {
        return $updates->run((int) $this->option('user'));
    }
}
