<?php

namespace Taecontrol\NodeGraph;

use Taecontrol\NodeGraph\Contracts\HasThread;

/**
 * Class Context
 *
 * @package Taecontrol\NodeGraph
 * @template TThread of Models\Thread
 * @implements HasThread<TThread>
 */
abstract class Context implements HasThread
{
    /**
     * Get the thread associated with the context.
     * @return TThread
     */
    abstract public function thread();
}
