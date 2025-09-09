<?php

namespace Taecontrol\NodeGraph\Contracts;

/**
 * Interface HasNode
 */
interface HasNode
{
    /**
     * Get the node associated with the state.
     */
    public function node(): Node;
}
