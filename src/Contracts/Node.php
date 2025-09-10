<?php

namespace Taecontrol\NodeGraph\Contracts;

/**
 * Interface Node
 *
 * @template TContext
 * @template TDecision
 */
interface Node
{
    /**
     * Handle the given data.
     *
     * @param  TContext  $data
     * @return TDecision
     */
    public function handle($data);
}
