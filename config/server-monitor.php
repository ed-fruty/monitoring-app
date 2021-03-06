<?php

return [

    /*
     * These are the checks that can be performed on your servers. You can add your own
     * checks. The only requirement is that they should extend the
     * `Spatie\ServerMonitor\Checks\CheckDefinitions\CheckDefinition` class.
     */
    'checks' => [
        'diskspace' => Spatie\ServerMonitor\CheckDefinitions\Diskspace::class,
        'elasticsearch' => Spatie\ServerMonitor\CheckDefinitions\Elasticsearch::class,
        'memcached' => Spatie\ServerMonitor\CheckDefinitions\Memcached::class,
        'mysql' => Spatie\ServerMonitor\CheckDefinitions\MySql::class,
        'mysql:replication' => \App\CheckDefinitions\Mysql\MysqlReplication::class,
        'memcached:process' => \App\CheckDefinitions\Memcached\MemcachedProcess::class,
        'mongo:process' => \App\CheckDefinitions\Mongo\MongoProcess::class,
        'rabbit-mq:process' => \App\CheckDefinitions\RabbitMQ\RabbitMQProcess::class,
        'redis:info' => \App\CheckDefinitions\Redis\RedisInfo::class,
        'redis:process' => \App\CheckDefinitions\Redis\RedisProcess::class,
        'supervisor:process' => \App\CheckDefinitions\Supervisor\SupervisorProcess::class,
        'supervisor:tasks' => \App\CheckDefinitions\Supervisor\SupervisorTasks::class,
    ],

    /*
     * The performance of the package can be increased by allowing a high number
     * of concurrent ssh connections. Set this to a lower value if you're
     * getting weird errors running the check.
     */
    'concurrent_ssh_connections' => 5,

    /*
     * This string will be prepended to the ssh command generated by the package.
     */
    'ssh_command_prefix' => '',

    /*
     * This string will be appended to the ssh command generated by the package.
     */
    'ssh_command_suffix' => '',

    'notifications' => [

        'notifications' => [
            \App\Notifications\CheckSucceeded::class => [],
            \App\Notifications\CheckRestored::class => ['slack', 'mail'],
            \App\Notifications\CheckWarning::class => ['slack', 'mail'],
            \App\Notifications\CheckFailed::class => ['slack', 'mail'],
        ],

        /*
         * To avoid burying you in notifications, we'll only send one every given amount
         * of minutes when a check keeps emitting warning or keeps failing.
         */
        'throttle_failing_notifications_for_minutes' => 60,

        // Separate the email by , to add many recipients
        'mail' => [
            'to' => env('MAIL_NOTIFY_LIST'),
        ],

        'slack' => [
            'webhook_url' => env('SERVER_MONITOR_SLACK_WEBHOOK_URL'),
        ],

        /*
         * Here you can specify the notifiable to which the notifications should be sent. The default
         * notifiable will use the variables specified in this config file.
         */
        'notifiable' => \Spatie\ServerMonitor\Notifications\Notifiable::class,

        /*
         * The date format used in notifications.
         */
        'date_format' => 'Y.m.d',
    ],

    /*
     * To add or modify behaviour to the `Host` model you can specify your
     * own model here. The only requirement is that they should
     * extend the `Host` model provided by this package.
     */
    'host_model' => \App\Host::class,

    /*
     * To add or modify behaviour to the `Check` model you can specify your
     * own model here. The only requirement is that they should
     * extend the `Check` model provided by this package.
     */
    'check_model' => \App\Check::class,

    /*
     * Right before running a check it's process will be given to this class. Here you
     * can perform some last minute manipulations on it before it will
     * actually be run.
     *
     * This class should implement Spatie\ServerMonitor\Manipulators\Manipulator
     */
    'process_manipulator' => Spatie\ServerMonitor\Manipulators\Passthrough::class,

    /*
     * Thresholds for disk space's alert.
     */
    'diskspace_percentage_threshold' => [
        'warning' => 80,
        'fail' => 90,
    ],
];
