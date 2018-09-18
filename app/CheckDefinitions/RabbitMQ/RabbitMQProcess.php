<?php

namespace App\CheckDefinitions\RabbitMQ;

use Spatie\Regex\Regex;
use Spatie\Regex\RegexFailed;
use Spatie\ServerMonitor\CheckDefinitions\CheckDefinition;
use Symfony\Component\Process\Process;

class RabbitMQProcess extends CheckDefinition
{
    /**
     * @var string
     */
    public $command = 'ps -e | grep rabbitmq';

    /**
     * @var string
     */
    public $pattern = '/rabbitmq[-_]server/';

    /**
     * @param Process $process
     * @return \Spatie\ServerMonitor\Models\Check|bool
     */
    public function resolve(Process $process): bool
    {
        try {
            if (Regex::match($this->pattern, $process->getOutput())) {
                $this->check->succeed('is running');

                return true;
            }
        } catch (RegexFailed $exception) {
            $this->check->fail('is not running');

            return false;
        }

        $this->check->warn('can\'t detect rabbitmq status');

        return false;
    }
}
