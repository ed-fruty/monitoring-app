<?php

namespace App\Repositories;

use App\Check;
use App\Contracts\CheckRepository;
use App\Queries\ServerMonitor\GetChecksThatShouldRun;
use Spatie\ServerMonitor\CheckCollection;

class EloquentCheckRepository implements CheckRepository
{
    /**
     * @var Check
     */
    private $checkSource;

    /**
     * EloquentCheckRepository constructor.
     * @param Check $checkSource
     */
    public function __construct(Check $checkSource)
    {
        $this->checkSource = $checkSource;
    }

    /**
     * @param GetChecksThatShouldRun $query
     * @return CheckCollection
     */
    public function getAllThatShouldRun(GetChecksThatShouldRun $query): CheckCollection
    {
        $checks = $this->checkSource->newQuery()
            ->with('host')
            ->enabled()
            ->get()
            ->filter(function (Check $check) use ($query) {

                if ($query->getHosts()->isNotEmpty() && false === $query->getHosts()->contains($check->host->name)) {
                    return false;
                }

                if ($query->getChecks()->isNotEmpty() && false === $query->getChecks()->contains($check->type)) {
                    return false;
                }

                return $query->isForce() ?: $check->shouldRun();
            });

        return new CheckCollection($checks);
    }
}
