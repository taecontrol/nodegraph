<?php

namespace Taecontrol\NodeGraph\Contracts;

/**
 * Interface Node
 *
 * @template T
 * @package Taecontrol\NodeGraph\Contracts
 */
interface Node
{
    /**
     * Handle the given data.
     *
     * @param T $data
     * @return T
     */
    public function handle($data);
}