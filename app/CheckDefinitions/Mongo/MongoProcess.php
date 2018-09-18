<?php

namespace App\CheckDefinitions\Mongo;

use Spatie\ServerMonitor\CheckDefinitions\CheckDefinition;
use Symfony\Component\Process\Process;

class MongoProcess extends CheckDefinition
{
    public $command = 'ps -e | grep mongo';

    public $needles = 'mongod';

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
