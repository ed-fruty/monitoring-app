<?php

namespace App\CheckDefinitions\Supervisor;

use Spatie\Regex\Regex;
use Spatie\Regex\RegexFailed;
use Spatie\ServerMonitor\CheckDefinitions\CheckDefinition;
use Symfony\Component\Process\Process;

class SupervisorTasks extends CheckDefinition
{
    public $command = 'supervisorctl status';

    public $pattern = '/(?P<task>\w+)\s+(?P<status>[FATAL|BACKOFF]+)/';

    /**
     * @param Process $process
     * @return bool
     */
    public function resolve(Process $process): bool
    {
        try {
            if ($match = Regex::match($this->pattern, $process->getOutput())) {
                $this->check->fail(sprintf(
                    'Errors in supervisor task "%s". Status: "%s"',
                    $match->group('task'),
                    $match->group('status')
                ));

                return false;
            }
        } catch (RegexFailed $exception) {
            $this->check->succeed('is running');

            return true;
        }

        $this->check->warn('can\'t detect supervisor tasks status');

        return true;
    }
}
