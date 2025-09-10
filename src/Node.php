<?php

namespace Taecontrol\NodeGraph;

/**
 * Class Node
 *
 * @template TContext of Context
 *
 * @implements Contracts\Node<TContext, Decision>
 */
abstract class Node implements Contracts\Node
{
    /**
     * Execute the node with the given data.
     *
     * @param  TContext  $data
     */
    public function execute($data): Decision
    {
        $startTime = microtime(true);

        $result = $this->handle($data);

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $result->addMetadata('state', $data->thread()->state);
        $result->addMetadata('execution_time', $executionTime);

        return $result;
    }

    /**
     * Handle the given data.
     *
     * @param  TContext  $data
     */
    abstract public function handle($data): Decision;
}
