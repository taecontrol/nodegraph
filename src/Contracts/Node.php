<?php

namespace Taecontrol\NodeGraph\Contracts;

use Taecontrol\NodeGraph\Context;
use Taecontrol\NodeGraph\Decision;

/**
 * Interface Node
 *
 * @template TContext of Context
 * @template TDecision of Decision
 * @package Taecontrol\NodeGraph\Contracts
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
