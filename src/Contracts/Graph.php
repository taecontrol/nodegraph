<?php

namespace Taecontrol\NodeGraph\Contracts;

use BackedEnum;
use InvalidArgumentException;

/**
 * Interface Graph
 */
interface Graph
{
    /**
     * Defines the structure of the graph.
     */
    public function define(): void;

    /**
     * Returns the initial state of the graph.
     */
    public function initialState(): BackedEnum&HasNode;

    /**
     * Adds a new State to the graph.
     */
    public function addState(BackedEnum&HasNode $state): void;

    /**
     * Adds a directed edge from one state to another.
     */
    public function addEdge(BackedEnum&HasNode $from, BackedEnum&HasNode $to): void;

    /**
     * Returns the neighboring states of a given state.
     * @return array<int, BackedEnum&HasNode>
     */
    public function neighbors(BackedEnum&HasNode $state): array;

    /**
     * Checks if a transition from one state to another is possible.
     */
    public function canTransition(BackedEnum&HasNode $from, BackedEnum&HasNode $to): bool;

    /**
     * Asserts that a transition from one state to another is possible.
     * @throws InvalidArgumentException if the transition is not allowed
     */
    public function assert(BackedEnum&HasNode $from, BackedEnum&HasNode $to): void;

    /**
     * Checks if the given state is a terminal state.
     */
    public function isTerminal(BackedEnum&HasNode $state): bool;
}
