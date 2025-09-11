<?php

namespace Taecontrol\NodeGraph\Tests\Fixtures;

use Taecontrol\NodeGraph\Node;

class StartNode extends Node
{
    public function handle($data): SimpleDecision
    {
        $decision = new SimpleDecision(SampleState::Middle);
        $decision->addMetadata('from', 'start');
        $decision->addEvent(new TestEvent('start'));

        return $decision;
    }
}
