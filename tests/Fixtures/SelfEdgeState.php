<?php

namespace Taecontrol\NodeGraph\Tests\Fixtures;

use Taecontrol\NodeGraph\Contracts\HasNode;

enum SelfEdgeState: string implements HasNode
{
    case Processing = 'processing';
    case Done = 'done';

    public function node(): string
    {
        return match ($this) {
            self::Processing => SelfEdgeNode::class,
            self::Done => EndNode::class,
        };
    }
}
