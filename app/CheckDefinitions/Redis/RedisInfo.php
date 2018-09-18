<?php

namespace App\CheckDefinitions\Redis;

use Spatie\Regex\Regex;
use Spatie\ServerMonitor\CheckDefinitions\CheckDefinition;
use Symfony\Component\Process\Process;

class RedisInfo extends CheckDefinition
{
    public const DEFAULT_PORT = 6379;

    public const CUSTOM_PORT = 'port';
    public const CUSTOM_INFO_USED_MEMORY_HUMAN = 'used_memory_human';
    public const CUSTOM_INFO_VERSION = 'redis_version';
    public const CUSTOM_INFO_UP_TIME_DAYS = 'uptime_in_days';

    public const CUSTOM_PROPERTIES = [
        self::CUSTOM_PORT,
        self::CUSTOM_INFO_USED_MEMORY_HUMAN,
        self::CUSTOM_INFO_VERSION,
        self::CUSTOM_INFO_UP_TIME_DAYS
    ];

    public const INFO_PROPERTIES = [
        self::CUSTOM_INFO_USED_MEMORY_HUMAN,
        self::CUSTOM_INFO_VERSION,
        self::CUSTOM_INFO_UP_TIME_DAYS
    ];

    /**
     * @var string
     */
    public $command = 'redis-cli -p %d info';

    /**
     * @return string
     */
    public function command(): string
    {
        $port = $this->check->getCustomProperty(self::CUSTOM_PORT, self::DEFAULT_PORT);

        return sprintf($this->command, $port);
    }

    /**
     * @param Process $process
     * @return bool
     * @throws \Spatie\Regex\RegexFailed
     */
    public function resolve(Process $process): bool
    {
        $messages = [];

        foreach (self::INFO_PROPERTIES as $property) {
            if ($this->check->hasCustomProperty($property)) {
                $messages[] = $this->parseInfo($process->getOutput(), $property);
            }
        }

        if (empty($messages)) {
            $this->check->fail('no info got.');

            return false;
        }

        $this->check->succeed(implode(PHP_EOL, $messages));

        return true;
    }

    /**
     * @param string $content
     * @param string $property
     * @return string
     * @throws \Spatie\Regex\RegexFailed
     */
    protected function parseInfo(string $content, string $property): string
    {
        $pattern = "/(?P<message>{$property}\:(?P<value>\S+))\s+/";

        $message = Regex::match($pattern, $content)->namedGroup('message');

        return str_replace(['_', ':'], [' ', ': '], ucfirst($message));
    }
}
