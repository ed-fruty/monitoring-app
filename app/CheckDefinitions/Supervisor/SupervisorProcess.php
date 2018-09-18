<?php

namespace App\CheckDefinitions\Supervisor;

use Spatie\ServerMonitor\CheckDefinitions\CheckDefinition;
use Symfony\Component\Process\Process;

class SupervisorProcess extends CheckDefinition
{
    /**
     * @var string
     */
    public $command = 'ps -e | grep supervisor';

    /**
     * @var string
     */
    public $needles = 'supervisord';

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
