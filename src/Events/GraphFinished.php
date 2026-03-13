<?php

namespace Taecontrol\NodeGraph\Events;

use BackedEnum;
use Taecontrol\NodeGraph\Models\Thread;

class GraphFinished
{
    public function __construct(
        public readonly Thread $thread,
        public readonly string $graphName,
        public readonly BackedEnum $finalState,
    ) {}
}
