<?php

namespace App\Console\Commands;

use App\Contracts\CheckRepository;
use Spatie\ServerMonitor\Commands\BaseCommand;
use App\Queries\ServerMonitor\GetChecksThatShouldRun;

class RunChecks extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'server-monitor:run {--f|force} {--host=*} {--check=*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run with different options';

    /**
     * Execute the console command.
     *
     * @param CheckRepository $checkRepository
     */
    public function handle(CheckRepository $checkRepository)
    {
        $checks = $checkRepository->getAllThatShouldRun(new GetChecksThatShouldRun(
            $this->option('host'),
            $this->option('check'),
            $this->option('force')
        ));

        $this->info(sprintf('Start running %d checks...', $checks->count()));

        $checks->runAll();

        $this->info('All done!');
    }
}
