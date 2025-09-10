<?php

namespace Taecontrol\NodeGraph\Contracts;

/**
 * Interface Node
 *
 * @template TData
 */
interface Node
{
    /**
     * Handle the given data.
     *
     * @param  TData  $data
     * @return TData
     */
    public function handle($data);
}
