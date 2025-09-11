<?php

namespace Taecontrol\NodeGraph\Contracts;

/**
 * Interface HasNode
 *
 * @template TNode of Node
 */
interface HasNode
{
    /**
     * Get the node associated with the state.
     *
     * @return class-string<TNode>
     */
    public function node(): string;
}
