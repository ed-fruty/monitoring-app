<?php

namespace App\Queries\ServerMonitor;

use Illuminate\Support\Collection;

class GetChecksThatShouldRun
{
    /**
     * @var Collection
     */
    private $hosts;
    /**
     * @var Collection
     */
    private $checks;
    /**
     * @var bool
     */
    private $force;

    /**
     * GetChecksThatShouldRun constructor.
     * @param array $hosts
     * @param array $checks
     * @param bool $force
     */
    public function __construct(array $hosts = [], array $checks = [], bool $force = false)
    {
        $this->hosts = collect($hosts);
        $this->checks = collect($checks);
        $this->force = $force;
    }

    /**
     * @return Collection
     */
    public function getHosts(): Collection
    {
        return $this->hosts;
    }

    /**
     * @return Collection
     */
    public function getChecks(): Collection
    {
        return $this->checks;
    }

    /**
     * @return bool
     */
    public function isForce(): bool
    {
        return $this->force;
    }
}
