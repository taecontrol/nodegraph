<?php

namespace Taecontrol\NodeGraph;

/**
 * Class Node
 *
 * @implements Contracts\Node<Context, Decision>
 */
abstract class Node implements Contracts\Node
{
    /**
     * Execute the node with the given data.
     */
    public function execute(Context $data): Decision
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
     */
    abstract public function handle(Context $data): Decision;
}
