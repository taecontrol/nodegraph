<?php

namespace Taecontrol\NodeGraph\Tests\Fixtures;

use Taecontrol\NodeGraph\Event as BaseEvent;

class TestEvent extends BaseEvent
{
    public function __construct(public ?string $name = null) {}
}
