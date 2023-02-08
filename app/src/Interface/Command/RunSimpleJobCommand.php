<?php

declare(strict_types=1);

namespace App\Interface\Command;

use App\Interface\Queue\SimpleJob;
use Spiral\Console\Command;
use Spiral\RoadRunner\Jobs\JobsInterface;

final class RunSimpleJobCommand extends Command
{
    private const QUEUE_NAME = 'amqp';

    protected const SIGNATURE = 'benchmark:memory-queue {--I|iteration=500000}';
    protected const DESCRIPTION = 'Run simple jobs';

    public function perform(JobsInterface $jobs): int
    {
        $this->info(
            \sprintf('Benchmarking jobs with Spiral Framework and RoadRunner with [%s] driver', self::QUEUE_NAME)
        );

        $queue = $jobs->connect(self::QUEUE_NAME);

        $queue->pause();

        $iteration = (int)$this->option('iteration');
        $this->info(\sprintf('Pushing [%s] jobs...', \number_format($iteration)));
        $bar = $this->output->createProgressBar($iteration);

        $fiber = new \Fiber(function (int $iteration) use ($queue, $bar) {
            for ($i = 0; $i < $iteration; $i++) {
                $bar->advance();
                $queue->push(SimpleJob::class, ['i' => $i]);
                \Fiber::suspend();
            }
        });

        try {
            $start = \microtime(true);
            $fiber->start($iteration);

            while (!$fiber->isTerminated()) {
                $fiber->resume();
            }

            $bar->finish();

            $this->newLine();

            $this->info(\sprintf('Pushed in [%f] seconds', \microtime(true) - $start));
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
        }

        $bar = $this->output->createProgressBar($total = $iteration);

        $this->info(\sprintf('Start processing [%s] jobs...', \number_format($iteration)));

        $queue->resume();

        $start = \microtime(true);

        $dataset = [];
        $i = 0;
        while (($current = $queue->getPipelineStat()->getActive()) > 0) {
            \usleep(50000);
            $bar->advance($total - $current);
            $dataset[] = [$i, $current];
            $total = $current;
            $i += 0.5;
        }
        $dataset[] = [$i, 0];

        $dataset = \implode("\n", \array_map(fn($row) => \implode(',', $row), $dataset));
        \file_put_contents('dataset.csv', $dataset);

        $bar->finish();

        $this->newLine();

        $this->info(\sprintf('Processed in [%f] seconds', \microtime(true) - $start));

        return self::SUCCESS;
    }
}
