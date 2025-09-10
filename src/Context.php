<?php

namespace Taecontrol\NodeGraph;

use Taecontrol\NodeGraph\Contracts\HasThread;

abstract class Context implements HasThread
{
    /**
     * Get the thread associated with the context.
     */
    abstract public function thread(): Models\Thread;
}
