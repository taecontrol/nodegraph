<?php

namespace Taecontrol\NodeGraph;

use BackedEnum;
use InvalidArgumentException;
use Taecontrol\NodeGraph\Contracts\HasNode;
use Taecontrol\NodeGraph\Models\Thread;

/**
 * Class Graph
 *
 * @template TState of (BackedEnum&HasNode)
 * @template TContext of Context
 * @template TDecision of Decision
 * @template TThread of Thread
 *
 * @implements Contracts\Graph<TState>
 */
abstract class Graph implements Contracts\Graph
{
    /**
     * @var array <int|string, list<TState>>
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
     *
     * @return TState
     */
    abstract public function initialState();

    /**
     * Runs the graph starting from the initial state.
     *
     * @param  TContext  $context
     */
    public function run($context): void
    {
        $thread = $context->thread();

        if ($thread->current_state === null) {
            $thread->current_state = $this->initialState();
            $thread->save();
        }

        /** @var TState $currentState */
        $currentState = $thread->current_state;

        /** @var Node $node */
        $node = app($currentState->node());

        $decision = $node->execute($context);

        if (! $this->isTerminal($currentState) && $decision->nextState() !== null) {
            $this->assert($currentState, $decision->nextState());
        }

        $thread = $this->updateThreadState($context, $decision->nextState());
        $this->createCheckpoint($thread, $decision);
        $this->dispatchEvents($decision);
    }

    /**
     * Adds a new State to the graph.
     *
     * @param  TState  $state
     */
    public function addState($state): void
    {
        if (! array_key_exists($state->value, $this->nodes)) {
            $this->nodes[$state->value] = [];
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

        if (! in_array($to, $this->nodes[$from->value], true)) {
            $this->nodes[$from->value][] = $to;
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
        return $this->nodes[$state->value] ?? [];
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
            throw new InvalidArgumentException("Invalid state transition: $from->value → $to->value");
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

    /**
     * Updates the current state of the thread in the context.
     *
     * @param  TContext  $context
     * @param  TState  $newState
     */
    protected function updateThreadState($context, $newState): Thread
    {
        $thread = $context->thread();

        if ($newState !== null) {
            $thread->current_state = $newState;
            $thread->save();
        }

        return $thread;
    }

    /**
     * Creates a checkpoint for the thread based on the decision.
     *
     * @param  TThread  $thread
     * @param  TDecision  $decision
     */
    protected function createCheckpoint($thread, $decision): void
    {
        $thread->checkpoints()->create([
            'state' => $decision->nextState(),
            'metadata' => $decision->metadata(),
        ]);
    }

    /**
     * Dispatches events associated with the decision.
     *
     * @param  TDecision  $decision
     */
    protected function dispatchEvents($decision): void
    {
        foreach ($decision->events() as $event) {
            event($event);
        }
    }
}
