<?php

namespace Taecontrol\NodeGraph\Contracts;

use BackedEnum;
use Taecontrol\NodeGraph\Exceptions\InvalidStateTransition;

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
    public function initialState(): BackedEnum;

    /**
     * Adds a new State to the graph.
     *
     * @param  TState  $state
     */
    public function addState($state): static;

    /**
     * Adds a directed edge from one state to another.
     *
     * @param  TState  $sourceState
     * @param  TState  $targetState
     */
    public function addEdge($sourceState, $targetState): static;

    /**
     * Returns the outgoing neighboring states of the given state.
     *
     * @param  TState  $state
     * @return array<int, TState>
     */
    public function neighborsOf($state): array;

    /**
     * Checks if a transition from one state to another is possible.
     *
     * @param  TState  $sourceState
     * @param  TState  $targetState
     */
    public function canTransition($sourceState, $targetState): bool;

    /**
     * Asserts that a transition from one state to another is valid.
     *
     * @param  TState  $sourceState
     * @param  TState  $targetState
     *
     * @throws InvalidStateTransition if the transition is not allowed
     */
    public function assertValidTransition($sourceState, $targetState): void;

    /**
     * Checks if the given state is a terminal state.
     *
     * @param  TState  $state
     */
    public function isTerminal($state): bool;
}
