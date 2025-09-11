<?php

namespace Taecontrol\NodeGraph\Tests\Fixtures;

use Taecontrol\NodeGraph\Graph as BaseGraph;

class TestGraph extends BaseGraph
{
    public function define(): void
    {
        $this->addEdge(SampleState::Start, SampleState::Middle);
        $this->addEdge(SampleState::Middle, SampleState::End);
        // End has no outgoing edges, making it terminal
    }

    public function initialState(): SampleState
    {
        return SampleState::Start;
    }
}
