<?php

namespace App\Contracts;

use App\Queries\ServerMonitor\GetChecksThatShouldRun;
use Spatie\ServerMonitor\CheckCollection;

interface CheckRepository
{
    /**
     * @param GetChecksThatShouldRun $query
     * @return CheckCollection
     */
    public function getAllThatShouldRun(GetChecksThatShouldRun $query): CheckCollection;
}
