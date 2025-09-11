<?php

namespace Taecontrol\NodeGraph\Tests\Fixtures;

use Taecontrol\NodeGraph\Context;
use Taecontrol\NodeGraph\Models\Thread;

class TestContext extends Context
{
    public function __construct(private Thread $thread) {}

    public function thread(): Thread
    {
        return $this->thread;
    }
}
