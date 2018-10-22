<?php

namespace App\CheckDefinitions\Mysql;

use Spatie\Regex\Regex;
use Spatie\Regex\RegexFailed;
use Symfony\Component\Process\Process;
use Spatie\ServerMonitor\Models\Check;
use Spatie\ServerMonitor\CheckDefinitions\CheckDefinition;

class MysqlReplication extends CheckDefinition
{
    public const DEFAULT_USERNAME = 'root';
    public const DEFAULT_PORT = 3306;
    public const DEFAULT_PASSWORD = 'secret';

    public const DEFAULT_WARNING_SECONDS_BEHIND_MASTER = 10;
    public const DEFAULT_ERROR_SECONDS_BEHIND_MASTER = 20;

    public const CUSTOM_PROPERTY_USERNAME = 'username';
    public const CUSTOM_PROPERTY_PASSWORD = 'password';
    public const CUSTOM_PROPERTY_PORT = 'port';
    public const CUSTOM_PROPERTY_WARNING_SECONDS_BEHIND_MASTER = 'warning_seconds_behind_master';
    public const CUSTOM_PROPERTY_ERROR_SECONDS_BEHIND_MASTER = 'error_seconds_behind_master';

    public const DEFAULTS = [
        self::DEFAULT_USERNAME,
        self::DEFAULT_PASSWORD,
        self::DEFAULT_PORT,
        self::DEFAULT_WARNING_SECONDS_BEHIND_MASTER,
        self::DEFAULT_ERROR_SECONDS_BEHIND_MASTER
    ];

    public const CUSTOM_PROPERTIES = [
        self::CUSTOM_PROPERTY_USERNAME,
        self::CUSTOM_PROPERTY_PASSWORD,
        self::CUSTOM_PROPERTY_PORT,
        self::CUSTOM_PROPERTY_WARNING_SECONDS_BEHIND_MASTER,
        self::CUSTOM_PROPERTY_ERROR_SECONDS_BEHIND_MASTER,
    ];

    public $command = '
        export MYSQL_PWD=%s && \
        export MYSQL_TCP_PORT=%s && \
        mysql -u%s -e "SHOW SLAVE STATUS\G"';

    private const REGEX = [
        'error_number' => '/Last_Errno\: (?P<error_number>.*)/',
        'error_message' => '/Last_Error\: (?P<error_message>.*)\s/',
        'behind_master' => '/Seconds_Behind_Master\: (?P<behind_master>.*)\s/',
        'io_is_running' => '/Slave_IO_Running\: (?P<io_is_running>.*)\s/',
        'sql_is_running' => '/Slave_SQL_Running\: (?P<sql_is_running>.*)\s/',
    ];

    /**
     * @return string
     */
    public function command(): string
    {
        $username = $this->check->hasCustomProperty(self::CUSTOM_PROPERTY_USERNAME)
            ?   $this->check->getCustomProperty(self::CUSTOM_PROPERTY_USERNAME)
            :   self::DEFAULT_USERNAME;

        $password = $this->check->hasCustomProperty(self::CUSTOM_PROPERTY_PASSWORD)
            ?   $this->check->getCustomProperty(self::CUSTOM_PROPERTY_PASSWORD)
            :   env('SERVER_MONITOR_DEFAULT_MYSQL_PASSWORD', self::DEFAULT_PASSWORD);

        $port = $this->check->hasCustomProperty(self::CUSTOM_PROPERTY_PORT)
            ?   $this->check->getCustomProperty(self::CUSTOM_PROPERTY_PORT)
            :   self::DEFAULT_PORT;

        return sprintf($this->command, $password, $port, $username);
    }

    /**
     * @param Process $process
     * @return \Spatie\ServerMonitor\Models\Check
     */
    public function resolve(Process $process): Check
    {
        try {
            $lastErrorNumber = (int) $this->parseProcessOutput($process, 'error_number');
            $lastErrorMessage = $this->parseProcessOutput($process, 'error_message');
            $secondsBehindMaster = $this->parseProcessOutput($process, 'behind_master');
            $isIORunning = $this->parseProcessOutput($process, 'io_is_running') === 'Yes';
            $isSqlRunning = $this->parseProcessOutput($process, 'sql_is_running') === 'Yes';

            if ($lastErrorNumber !== 0) {
                return $this->check->fail(sprintf('Last error message: %s', $lastErrorMessage));
            }

            if (false === $isIORunning) {
                return $this->check->fail(
                    'I/O thread for reading the master\'s binary log is not running (Slave_IO_Running)'
                );
            }

            if (false === $isSqlRunning) {
                return $this->check->fail(
                    'SQL thread for executing events in the relay log is not running (Slave_SQL_Running)'
                );
            }

            if ($secondsBehindMaster === 'NULL') {
                return $this->check->fail(
                    'The Slave is reporting \'NULL\' (Seconds_Behind_Master)'
                );
            }

            if ((int) $secondsBehindMaster > $limit = $this->getErrorLimitForBehindMaster()) {
                return $this->check->fail(sprintf(
                    'Error. The Slave is at least %d seconds behind the master (Seconds_Behind_Master)',
                    $limit
                ));
            }

            if ((int) $secondsBehindMaster > $limit = $this->getWarningLimitForBehindMaster()) {
                return $this->check->warn(sprintf(
                    'Warning. The Slave is at least %d seconds behind the master (Seconds_Behind_Master)',
                    $limit
                ));
            }

            $messages = [];
            $messages[] = sprintf('Seconds behind master: %d', (int) $secondsBehindMaster);
            $messages[] = sprintf('Is IO running: %d', (int) $isIORunning);
            $messages[] = sprintf('Is SQL running: %d', (int) $isIORunning);
            $messages[] = sprintf('Last error number: %d', $lastErrorNumber);

            return $this->check->succeed(implode(PHP_EOL, $messages));

        } catch (RegexFailed $exception) {
            return $this->check->fail($exception->getMessage());
        }
    }

    /**
     * @return int
     */
    private function getErrorLimitForBehindMaster(): int
    {
        return $this->check->hasCustomProperty(self::CUSTOM_PROPERTY_ERROR_SECONDS_BEHIND_MASTER)
            ?  (int) $this->check->getCustomProperty(self::CUSTOM_PROPERTY_ERROR_SECONDS_BEHIND_MASTER)
            :  self::DEFAULT_ERROR_SECONDS_BEHIND_MASTER;
    }

    /**
     * @return int
     */
    private function getWarningLimitForBehindMaster(): int
    {
        return $this->check->hasCustomProperty(self::CUSTOM_PROPERTY_WARNING_SECONDS_BEHIND_MASTER)
            ?   (int) $this->check->getCustomProperty(self::CUSTOM_PROPERTY_WARNING_SECONDS_BEHIND_MASTER)
            :   self::DEFAULT_WARNING_SECONDS_BEHIND_MASTER;
    }

    /**
     * @param Process $process
     * @param string $regexGroup
     * @param bool $isErrorOutput
     * @return string
     * @throws RegexFailed
     * @throws \InvalidArgumentException
     */
    private function parseProcessOutput(Process $process, string $regexGroup, bool $isErrorOutput = false): string
    {
        if (! isset(self::REGEX[$regexGroup])) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid regex group: %s',
                $regexGroup
            ));
        }

        $output = $isErrorOutput ? $process->getErrorOutput() : $process->getOutput();

        return Regex::match(self::REGEX[$regexGroup], $output)->group($regexGroup);
    }
}
