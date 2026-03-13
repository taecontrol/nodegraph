<?php

namespace Taecontrol\NodeGraph;

use BackedEnum;
use Taecontrol\NodeGraph\Contracts\HasNode;
use Taecontrol\NodeGraph\Events\GraphFinished;
use Taecontrol\NodeGraph\Exceptions\InvalidStateTransition;
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

        $isNewThread = $thread->current_state === null;

        if ($isNewThread) {
            $thread->current_state = $this->initialState();
            $thread->started_at = now();
        }

        /** @var TState $currentState */
        $currentState = $thread->current_state;

        if ($this->isTerminal($currentState)) {
            return;
        }

        /** @var Node $node */
        $node = app($currentState->node());
        $decision = $node->execute($context);

        // Validate transition BEFORE any side effects
        $this->assertValidTransition($currentState, $decision->nextState());

        $isTerminal = $this->isTerminal($decision->nextState());

        $thread->getConnection()->transaction(function () use ($thread, $decision, $isNewThread, $isTerminal) {
            if (! $isNewThread) {
                $thread = $thread->lockForUpdate()->findOrFail($thread->id);
            }

            $this->updateThreadMetadata($thread, $decision->metadata());
            $this->createCheckpoint($thread, $decision);

            $thread->current_state = $decision->nextState();

            if ($isTerminal) {
                $thread->finished_at = now();
            }

            $thread->save();
        });

        $thread->refresh();

        $this->dispatchEvents($decision);

        if ($isTerminal) {
            event(new GraphFinished($thread, $thread->graph_name, $decision->nextState()));
        }
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
     * @throws InvalidStateTransition if the transition is not allowed
     */
    public function assertValidTransition($from, $to): void
    {
        if (! $this->canTransition($from, $to)) {
            throw new InvalidStateTransition($from, $to, $this->neighbors($from));
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
     * Updates the metadata of the thread.
     *
     * @param  TThread  $thread
     * @param  array<string, mixed>  $metadata
     */
    protected function updateThreadMetadata($thread, array $metadata): void
    {
        $thread->metadata = array_merge($thread->metadata ?? [], [
            $thread->current_state->value => $metadata,
        ]);
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
            'graph_name' => $thread->graph_name,
            'state' => $decision->nextState(),
            'metadata' => array_merge($thread->metadata ?? [], $decision->metadata()),
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
