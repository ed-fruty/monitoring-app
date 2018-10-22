<?php

namespace App\Console\Commands;

use App\Check;
use App\Common\Loader\SyncFileLoader;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Spatie\ServerMonitor\Commands\SyncFile;
use Spatie\ServerMonitor\Models\Host;

class SyncFileWithCustomProperties extends SyncFile
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'server-monitor:sync-file-extended
                            {path : Path to JSON or YAML file with hosts}
                            {--delete-missing : Delete hosts from the database which are not in the hosts file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Allow to sync file with custom properties in checks and hosts.';
    /**
     * @var SyncFileLoader
     */
    private $fileLoader;

    /**
     * SyncFileWithCustomProperties constructor.
     * @param SyncFileLoader $fileLoader
     */
    public function __construct(SyncFileLoader $fileLoader)
    {
        $this->fileLoader = $fileLoader;
        parent::__construct();
    }

    public function handle()
    {
        $hostsInFile = $this->getHostsInFile($this->argument('path'));

        $this->createOrUpdateHostsFromFile($hostsInFile);

        $this->deleteMissingHosts($hostsInFile);
    }

    /**
     * @param Host $host
     * @param array $checkTypes
     */
    protected function removeChecksNotInArray(Host $host, array $checkTypes)
    {
        $checkTypes = collect($checkTypes)
            ->map(function ($checkType) {
                return $this->detectCheckType($checkType);
            })
            ->toArray();

        return parent::removeChecksNotInArray($host, $checkTypes);
    }

    /**
     * @param Host $host
     * @param array $checkTypes
     */
    protected function addChecksFromArray(Host $host, array $checkTypes)
    {
        collect($checkTypes)
            ->reject(function ($checkType) use ($host) {
                /** @var \App\Host $host */

                return $host->hasCheckTypeWithConcreteCustomProperties(
                    $this->detectCheckType($checkType),
                    $this->detectCheckCustomProperties($checkType)
                );
            })
            ->each(function ($checkType) use ($host) {

                /** @var Check $check */
                $check = $host->checks()->firstOrNew([
                    'type' => $this->detectCheckType($checkType),
                ]);

                $check->flushCustomProperties();

                foreach ($this->detectCheckCustomProperties($checkType) as $key => $value) {
                    $check->setCustomProperty($key, $value);
                }

                $host->checks()->save($check);
            });
    }

    /**
     * @param $checkType
     * @return mixed
     */
    protected function detectCheckType($checkType)
    {
        if (\is_string($checkType)) {
            return $checkType;
        }

        if (\is_array($checkType) && \count($checkType) === 1) {

            $collection = collect($checkType);
            $firstKey = $collection->keys()->first();
            $firstValue = $collection->values()->first();

            return (string) (\is_numeric($firstKey) && \is_string($firstValue))
                ?  $firstValue
                :  $firstKey;
        }

        throw new \InvalidArgumentException('Invalid check type was detected.');
    }

    /**
     * @param $check
     * @return array
     */
    protected function detectCheckCustomProperties($check): array
    {
        return \is_array($check) && isset($check[$this->detectCheckType($check)])
            ?   $check[$this->detectCheckType($check)]
            :   [];
    }

    /**
     * @param $filename
     * @return Collection
     */
    protected function getHostsInFile($filename): Collection
    {
        $content = $this->fileLoader->import($filename);
        $items = $content['hosts'] ?? $content;

        return collect($items);
    }
}
