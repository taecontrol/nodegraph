<?php

namespace Taecontrol\NodeGraph\Tests\Fixtures;

use Taecontrol\NodeGraph\Node;

class EndNode extends Node
{
    public function handle($data): SimpleDecision
    {
        $decision = new SimpleDecision(null);
        $decision->addMetadata('from', 'end');
        $decision->addEvent(new TestEvent('end'));

        return $decision;
    }
}
