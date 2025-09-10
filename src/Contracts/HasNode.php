<?php

namespace Taecontrol\NodeGraph\Contracts;

/**
 * Interface HasNode
 */
interface HasNode
{
    /**
     * Get the node associated with the state.
     * @return class-string<Node>
     */
    public function node(): string;
}
