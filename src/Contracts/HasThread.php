<?php

namespace Taecontrol\NodeGraph\Contracts;

use Taecontrol\NodeGraph\Models\Thread;

/**
 * Interface HasNode
 */
interface HasThread
{
    /**
     * Get the node associated with the state.
     */
    public function thread(): Thread;
}
