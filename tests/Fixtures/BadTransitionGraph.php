<?php

namespace Taecontrol\NodeGraph\Tests\Fixtures;

use Taecontrol\NodeGraph\Graph as BaseGraph;

class BadTransitionGraph extends BaseGraph
{
    public function define(): void
    {
        $this->addEdge(BadTransitionState::A, BadTransitionState::B);
    }

    public function initialState(): BadTransitionState
    {
        return BadTransitionState::A;
    }
}
