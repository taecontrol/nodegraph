<?php

namespace Taecontrol\NodeGraph\Contracts;

/**
 * Interface Node
 *
 * @template T
 */
interface Node
{
    /**
     * Handle the given data.
     *
     * @param  T  $data
     * @return T
     */
    public function handle($data);
}
