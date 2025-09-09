<?php

namespace Taecontrol\NodeGraph;

use InvalidArgumentException;

/**
 * Class Graph
 *
 * @template TState
 */
abstract class Graph implements Contracts\Graph
{
    /**
     * @var array <int|string, list<TState>>
     */
    private array $nodes = [];

    /**
     * Adds a new State to the graph.
     *
     * @param  TState  $state
     */
    public function addState($state): void
    {
        if (! array_key_exists($state, $this->nodes)) {
            $this->nodes[$state] = [];
        }
    }

    /**
     * Adds a directed edge from one state to another.
     *
     * @param  TState  $from
     * @param  TState  $to
     */
    public function addEdge($from, $to): void
    {
        $this->addState($from);
        $this->addState($to);

        if (! in_array($to, $this->nodes[$from], true)) {
            $this->nodes[$from][] = $to;
        }
    }

    /**
     * Returns the neighboring states of a given state.
     *
     * @param  TState  $state
     * @return array<int, TState>
     */
    public function neighbors($state): array
    {
        return $this->nodes[$state] ?? [];
    }

    /**
     * Checks if a transition from one state to another is possible.
     *
     * @param  TState  $from
     * @param  TState  $to
     */
    public function canTransition($from, $to): bool
    {
        return in_array($to, $this->neighbors($from), true);
    }

    /**
     * Asserts that a transition from one state to another is valid.
     *
     * @param  TState  $from
     * @param  TState  $to
     *
     * @throws InvalidArgumentException if the transition is not allowed
     */
    public function assert($from, $to): void
    {
        if (! $this->canTransition($from, $to)) {
            throw new InvalidArgumentException("Invalid state transition: {$from} → {$to}");
        }
    }

    /**
     * Checks if the given state is a terminal state.
     *
     * @param  TState  $state
     */
    public function isTerminal($state): bool
    {
        return $this->neighbors($state) === [];
    }
}
