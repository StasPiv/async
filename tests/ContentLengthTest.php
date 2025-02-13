<?php

namespace Spatie\Async\Tests;

use Spatie\Async\Pool;
use PHPUnit\Framework\TestCase;
use Spatie\Async\Output\ParallelError;

class ContentLengthTest extends TestCase
{
    /** @test */
    public function it_can_increase_max_content_length()
    {
        $pool = Pool::create();

        $longerContentLength = 1024 * 100;

        $pool->add(new MyTask(), $longerContentLength);

        $this->assertContains('finished: 0', (string) $pool->status());

        await($pool);

        $this->assertContains('finished: 1', (string) $pool->status());
    }

    /** @test */
    public function it_can_decrease_max_content_length()
    {
        $pool = Pool::create();

        $shorterContentLength = 1024;

        $pool->add(new MyTask(), $shorterContentLength);

        $this->assertContains('finished: 0', (string) $pool->status());

        await($pool);

        $this->assertContains('finished: 1', (string) $pool->status());
    }

    /** @test */
    public function it_can_throw_error_with_increased_max_content_length()
    {
        $pool = Pool::create();

        $longerContentLength = 1024 * 100;

        $pool->add(function () {
            return random_bytes(1024 * 1000);
        }, $longerContentLength)
            ->catch(function (ParallelError $e) use ($longerContentLength) {
                $message = "/The output returned by this child process is too large. The serialized output may only be $longerContentLength bytes long./";
                $this->assertRegExp($message, $e->getMessage());
            });

        await($pool);
    }

    /** @test */
    public function it_can_throw_error_with_decreased_max_content_length()
    {
        $pool = Pool::create();

        $longerContentLength = 1024;

        $pool->add(function () {
            return random_bytes(1024 * 100);
        }, $longerContentLength)
            ->catch(function (ParallelError $e) use ($longerContentLength) {
                $message = "/The output returned by this child process is too large. The serialized output may only be $longerContentLength bytes long./";
                $this->assertRegExp($message, $e->getMessage());
            });

        await($pool);
    }
}
