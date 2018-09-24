<?php

namespace App\Console\Commands;

use App\Check;
use Illuminate\Support\Collection;
use Spatie\ServerMonitor\Commands\ListChecks;

class ListChecksExtended extends ListChecks
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'server-monitor:list-checks-extended
                            {--grep=* : Search value in result set}
                            {--host=* : Only show checks for certain host}
                            {--check=* : Only show certain check type}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Extended List all checks';

    /**
     * @param Collection $checks
     * @return array
     */
    protected function getTableRows(Collection $checks): array
    {
        return $checks
            ->when($this->option('host'), function (Collection $checks) {

                return $checks->filter(function (Check $check) {
                    return \in_array($check->host->name, (array) $this->option('host'), true);
                });
            })
            ->when($this->option('check'), function (Collection $checks) {

               return $checks->filter(function (Check $check) {
                    return \in_array($check->type, (array) $this->option('check'), true);
                });
            })
            ->map(function (Check $check) {

                return [
                    'name' => $check->host->name,
                    'check' => $check->type,
                    'last_run_message' => $check->last_run_message,
                    'status' => $check->getStatusAsEmojiAttribute(),
                    'last_checked' => $check->getLatestRunDiffAttribute(),
                    'next_check' => $check->getNextRunDiffAttribute(),
                ];
            })
            ->when($this->option('grep'), function (Collection $items) {

                return $items->filter(function ($item) {

                    foreach ($item as $key => $value) {
                        if (str_contains($value, (array) $this->option('grep'))) {
                            return true;
                        }
                    }

                    return false;
                });
            })
            ->toArray();
    }
}
