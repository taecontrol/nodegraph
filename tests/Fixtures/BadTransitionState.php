<?php

namespace Taecontrol\NodeGraph\Tests\Fixtures;

use Taecontrol\NodeGraph\Contracts\HasNode;

enum BadTransitionState: string implements HasNode
{
    case A = 'a';
    case B = 'b';
    case C = 'c';

    public function node(): string
    {
        return match ($this) {
            self::A => BadTransitionNode::class,
            self::B => EndNode::class,
            self::C => EndNode::class,
        };
    }
}
