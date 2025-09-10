<?php

namespace Taecontrol\NodeGraph\Contracts;

/**
 * Interface HasNode
 */
interface HasState
{
    /**
     * Get the node associated with the state.
     */
    public function state(): self;
}
