<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Spatie\ServerMonitor\Models\Check as SpatieCheck;

/**
 * Class Check
 * @package App
 *
 * @property Host $host
 *
 * @property int $id
 * @property int $host_id
 * @property string $type
 * @property string $status
 * @property bool $enabled
 * @property string $last_run_message
 * @property array $last_run_output
 * @property Carbon $last_ran_at
 * @property Carbon $next_check_at
 * @property int $next_run_in_minutes
 * @property Carbon $started_throttling_failing_notifications_at
 * @property array $custom_properties
 *
 * @method self|Builder enabled()
 * @method self|Builder newQuery()
 */
class Check extends SpatieCheck
{
    /**
     * Attribute casting.
     *
     * @var array
     */
    public $casts = [
        'custom_properties' => 'array',
        'last_run_output' => 'array',
        'id' => 'int',
        'host_id' => 'int',
        'enabled' => 'bool'
    ];

    /**
     * @return $this
     */
    public function flushCustomProperties(): self
    {
        $this->custom_properties = [];

        return $this;
    }
}
