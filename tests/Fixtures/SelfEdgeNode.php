<?php

namespace Taecontrol\NodeGraph\Tests\Fixtures;

use Taecontrol\NodeGraph\Node;

class SelfEdgeNode extends Node
{
    public function handle($data): SimpleDecision
    {
        $decision = new SimpleDecision(SelfEdgeState::Done);
        $decision->addMetadata('from', 'processing');
        $decision->addEvent(new TestEvent('processing'));

        return $decision;
    }
}
