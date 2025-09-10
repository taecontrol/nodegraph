<?php

namespace Taecontrol\NodeGraph;

/**
 * Class Node
 *
 * @template TContext of Context
 * @template TDecision of Decision
 *
 * @implements Contracts\Node<TContext, TDecision>
 */
abstract class Node implements Contracts\Node
{
    /**
     * Execute the node with the given data.
     *
     * @param  TContext  $data
     * @return TDecision
     */
    public function execute($data)
    {
        $startTime = microtime(true);

        $result = $this->handle($data);

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $result->addMetadata('state', $data->thread()->current_state);
        $result->addMetadata('execution_time', $executionTime);

        return $result;
    }

    /**
     * Handle the given data.
     *
     * @param  TContext  $data
     * @return TDecision
     */
    abstract public function handle($data);
}
