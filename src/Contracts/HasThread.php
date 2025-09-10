<?php

namespace Taecontrol\NodeGraph\Contracts;

use Taecontrol\NodeGraph\Models\Thread;

/**
 * Interface HasNode
 * @template TThread of Thread
 */
interface HasThread
{
    /**
     * Get the node associated with the state.
     * @return TThread
     */
    public function thread();
}
