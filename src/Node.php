<?php

namespace Taecontrol\NodeGraph;

use Taecontrol\NodeGraph\Contracts\HasState;

/**
 * Class Node
 *
 * @template TContext of HasState
 *
 * @implements Contracts\Node<TContext, Decision>
 */
abstract class Node implements Contracts\Node
{
    /**
     * Execute the node with the given data.
     *
     * @param TContext $data
     */
    public function execute(HasState $data): Decision
    {
        $startTime = microtime(true);

        $result = $this->handle($data);

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $result->addMetadata('state', $data->state());
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