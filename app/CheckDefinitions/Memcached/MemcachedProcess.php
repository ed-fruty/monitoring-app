<?php

namespace App\CheckDefinitions\Memcached;

use Spatie\ServerMonitor\CheckDefinitions\CheckDefinition;
use Symfony\Component\Process\Process;

class MemcachedProcess extends CheckDefinition
{
    /**
     * @var string
     */
    public $command = 'ps -e | grep memcache';

    /**
     * @var string
     */
    public $needles = 'memcached';

    /**
     * @param Process $process
     * @return bool
     */
    public function resolve(Process $process): bool
    {
        if (str_contains($process->getOutput(), $this->needles)) {
            $this->check->succeed('is running');

            return true;
        }

        $this->check->fail('is not running');

        return false;
    }
}
