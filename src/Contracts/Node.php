<?php

namespace Taecontrol\NodeGraph\Contracts;

use Taecontrol\NodeGraph\Context;
use Taecontrol\NodeGraph\Decision;

/**
 * Interface Node
 */
interface Node
{
    /**
     * Handle the given data.
     */
    public function handle(Context $data): Decision;
}
