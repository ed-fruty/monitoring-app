<?php

namespace App;

use Illuminate\Database\Eloquent\Collection;
use Spatie\ServerMonitor\Models\Host as SpatieHost;

/**
 * Class Host
 * @package App
 *
 * @property int $id
 * @property string $name
 * @property string $ssh_user
 * @property int $port
 * @property string $ip
 * @property array $custom_properties
 *
 * @property Check[]|Collection $checks
 */
class Host extends SpatieHost
{
    /**
     * Attribute casting.
     *
     * @var array
     */
    public $casts = [
        'custom_properties' => 'array',
        'id' => 'int',
        'int' => 'port'
    ];

    /**
     * @param string $type
     * @param array $properties
     * @return bool
     */
    public function hasCheckTypeWithConcreteCustomProperties(string $type, array $properties = []): bool
    {
        return $this->checks->contains(function (Check $check) use ($type, $properties) {

            return $check->type === $type
                && serialize($properties) === serialize((array) $check->custom_properties);
        });
    }
}
