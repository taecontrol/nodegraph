<?php

namespace Taecontrol\NodeGraph\Contracts;

/**
 * Interface HasNode
 *
 * @package Taecontrol\NodeGraph\Contracts
 */
interface HasNode
{
    /**
     * Get the node associated with the state.
     *
     * @return Node
     */
    public function node(): Node;
}