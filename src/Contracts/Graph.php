<?php

namespace Taecontrol\NodeGraph\Contracts;

use InvalidArgumentException;

/**
 * Interface Graph
 *
 * @template TState
 * @package Taecontrol\NodeGraph\Contracts
 */
interface Graph
{
    /**
     * Returns the initial state of the graph.
     *
     * @return TState $state
     */
    public function initialState();

    /**
     * @param TState $state
     * @return void
     */
    public function addState($state): void;

    /**
     * @param TState $from
     * @param TState $to
     * @return void
     */
    public function addEdge($from, $to): void;

    /**
     * @param TState $state
     * @return array<int, TState>
     */
    public function neighbors($state): array;

    /**
     * @param TState $from
     * @param TState $to
     * @return bool
     */
    public function canTransition($from, $to): bool;

    /**
     * @param TState $from
     * @param TState $to
     * @return void
     *
     * @throws InvalidArgumentException if the transition is not allowed
     */
    public function assert($from, $to): void;

    /**
     * Checks if the given state is a terminal state.
     *
     * @param TState $state
     * @return bool
     */
    public function isTerminal($state): bool;

}