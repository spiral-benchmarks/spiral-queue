<?php

declare(strict_types=1);

use Spiral\Queue\Driver\SyncDriver;
use Spiral\RoadRunner\Jobs\Queue\MemoryCreateInfo;
use Spiral\RoadRunner\Jobs\Queue\AMQPCreateInfo;
use Spiral\RoadRunner\Jobs\Queue\BeanstalkCreateInfo;
use Spiral\RoadRunner\Jobs\Queue\SQSCreateInfo;
use Spiral\RoadRunnerBridge\Queue\Queue;

return [
    'default' => env('QUEUE_CONNECTION', 'roadrunner'),

    'connections' => [
        'roadrunner' => [
            'driver' => 'roadrunner',
            'default' => 'amqp',
            'pipelines' => [],
        ],
    ],

    'driverAliases' => [
        'sync' => SyncDriver::class,
        'roadrunner' => Queue::class,
    ],

    'registry' => [
        'handlers' => [],
    ],
];
