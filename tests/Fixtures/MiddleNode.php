<?php

namespace Taecontrol\NodeGraph\Tests\Fixtures;

use Taecontrol\NodeGraph\Node;

class MiddleNode extends Node
{
    public function handle($data): SimpleDecision
    {
        $decision = new SimpleDecision(SampleState::End);
        $decision->addMetadata('from', 'middle');
        $decision->addEvent(new TestEvent('middle'));

        return $decision;
    }
}
