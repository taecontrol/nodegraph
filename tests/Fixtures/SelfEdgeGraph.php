<?php

namespace Taecontrol\NodeGraph\Tests\Fixtures;

use Taecontrol\NodeGraph\Graph as BaseGraph;

class SelfEdgeGraph extends BaseGraph
{
    public function define(): void
    {
        $this->addEdge(SelfEdgeState::Processing, SelfEdgeState::Processing);
        $this->addEdge(SelfEdgeState::Processing, SelfEdgeState::Done);
    }

    public function initialState(): SelfEdgeState
    {
        return SelfEdgeState::Processing;
    }
}
