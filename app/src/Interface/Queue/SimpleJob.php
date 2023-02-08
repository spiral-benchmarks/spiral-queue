<?php

declare(strict_types=1);

namespace App\Interface\Queue;

use Spiral\Queue\JobHandler;

final class SimpleJob extends JobHandler
{
    public function invoke(): void
    {
        \md5('test');
    }
}
