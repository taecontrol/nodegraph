<?php

namespace Taecontrol\NodeGraph;

use BackedEnum;
use InvalidArgumentException;
use Taecontrol\NodeGraph\Contracts\HasNode;
use Taecontrol\NodeGraph\Models\Thread;

/**
 * Class Graph
 */
abstract class Graph implements Contracts\Graph
{
    /**
     * @var array <int|string, list<BackedEnum&HasNode>>
     */
    private array $nodes = [];

    public function __construct()
    {
        $this->define();
    }

    /**
     * Defines the structure of the graph.
     */
    abstract public function define(): void;

    /**
     * Returns the initial state of the graph.
     */
    abstract public function initialState(): BackedEnum&HasNode;

    public function run(Thread $thread, Context $context): void
    {
        if ($thread->current_state === null) {
            $thread->current_state = $this->initialState()->value;
            $thread->save();
        }

        /** @var Node $node */
        $node = app($this->initialState()->node());

        $node->execute($context);
    }

    /**
     * Adds a new State to the graph.
     */
    public function addState(BackedEnum&HasNode $state): void
    {
        if (! array_key_exists($state->value, $this->nodes)) {
            $this->nodes[$state->value] = [];
        }
    }

    /**
     * Adds a directed edge from one state to another.
     */
    public function addEdge(BackedEnum&HasNode $from, BackedEnum&HasNode $to): void
    {
        $this->addState($from);
        $this->addState($to);

        if (! in_array($to, $this->nodes[$from->value], true)) {
            $this->nodes[$from->value][] = $to;
        }
    }

    /**
     * Returns the neighboring states of a given state.
     *
     * @return array<int, BackedEnum&HasNode>
     */
    public function neighbors(BackedEnum&HasNode $state): array
    {
        return $this->nodes[$state->value] ?? [];
    }

    /**
     * Checks if a transition from one state to another is possible.
     */
    public function canTransition(BackedEnum&HasNode $from, BackedEnum&HasNode $to): bool
    {
        return in_array($to, $this->neighbors($from), true);
    }

    /**
     * Asserts that a transition from one state to another is valid.
     *
     * @throws InvalidArgumentException if the transition is not allowed
     */
    public function assert(BackedEnum&HasNode $from, BackedEnum&HasNode $to): void
    {
        if (! $this->canTransition($from, $to)) {
            throw new InvalidArgumentException("Invalid state transition: $from->value → $to->value");
        }
    }

    /**
     * Checks if the given state is a terminal state.
     */
    public function isTerminal(BackedEnum&HasNode $state): bool
    {
        return $this->neighbors($state) === [];
    }
}
