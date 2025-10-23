<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\Wialon\ProcessUnitsJob;

class WialonGetCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wialon:get';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Iniciar la obtención de unidades desde Wialon';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        ProcessUnitsJob::dispatch();
    }
}
