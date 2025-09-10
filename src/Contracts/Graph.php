<?php

namespace Taecontrol\NodeGraph\Contracts;

use BackedEnum;
use InvalidArgumentException;

/**
 * Interface Graph
 *
 * @template TState of (BackedEnum&HasNode)
 */
interface Graph
{
    /**
     * Defines the structure of the graph.
     */
    public function define(): void;

    /**
     * Returns the initial state of the graph.
     *
     * @return TState
     */
    public function initialState();

    /**
     * Adds a new State to the graph.
     *
     * @param  TState  $state
     */
    public function addState($state): void;

    /**
     * Adds a directed edge from one state to another.
     *
     * @param  TState  $from
     * @param  TState  $to
     */
    public function addEdge($from, $to): void;

    /**
     * Returns the neighboring states of a given state.
     *
     * @param  TState  $state
     * @return array<int, TState>
     */
    public function neighbors($state): array;

    /**
     * Checks if a transition from one state to another is possible.
     *
     * @param  TState  $from
     * @param  TState  $to
     */
    public function canTransition($from, $to): bool;

    /**
     * Asserts that a transition from one state to another is possible.
     *
     * @param  TState  $from
     * @param  TState  $to
     *
     * @throws InvalidArgumentException if the transition is not allowed
     */
    public function assert($from, $to): void;

    /**
     * Checks if the given state is a terminal state.
     *
     * @param  TState  $state
     */
    public function isTerminal($state): bool;
}
