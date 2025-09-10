<?php

namespace Taecontrol\NodeGraph\Contracts;

use Taecontrol\NodeGraph\Context;
use Taecontrol\NodeGraph\Decision;

/**
 * Interface Node
 *
 * @template TContext of Context
 */
interface Node
{
    /**
     * Handle the given data.
     *
     * @param  TContext  $data
     */
    public function handle($data): Decision;
}
