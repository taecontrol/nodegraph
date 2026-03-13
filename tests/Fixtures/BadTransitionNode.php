<?php

namespace Taecontrol\NodeGraph\Tests\Fixtures;

use Taecontrol\NodeGraph\Node;

class BadTransitionNode extends Node
{
    public function handle($data): SimpleDecision
    {
        $decision = new SimpleDecision(BadTransitionState::C);
        $decision->addMetadata('from', 'a');
        $decision->addEvent(new TestEvent('a'));

        return $decision;
    }
}
