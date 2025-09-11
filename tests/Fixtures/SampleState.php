<?php

namespace Taecontrol\NodeGraph\Tests\Fixtures;

use Taecontrol\NodeGraph\Contracts\HasNode;

enum SampleState: string implements HasNode
{
    case Start = 'start';
    case Middle = 'middle';
    case End = 'end';

    public function node(): string
    {
        return match ($this) {
            self::Start => StartNode::class,
            self::Middle => MiddleNode::class,
            self::End => EndNode::class,
        };
    }
}
