<?php

namespace Taecontrol\NodeGraph\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Taecontrol\NodeGraph\NodeGraph
 */
class NodeGraph extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Taecontrol\NodeGraph\NodeGraph::class;
    }
}
