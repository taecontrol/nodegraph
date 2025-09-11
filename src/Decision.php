<?php

namespace Taecontrol\NodeGraph;

use BackedEnum;
use Taecontrol\NodeGraph\Contracts\HasNode;

/**
 * Class Decision
 *
 * @template TState of (BackedEnum&HasNode)
 * @template TEvent of Event
 */
abstract class Decision implements Contracts\HasMetadata
{
    public function __construct(
        /** @var TState|null */
        protected $nextState = null,
        /** @var array<string, mixed> */
        protected array $metadata = [],
        /** @var array<int, TEvent> */
        protected $events = []
    ) {}

    /**
     * Get the metadata associated with the decision.
     *
     * @return array<string, mixed>
     */
    public function metadata(): array
    {
        return $this->metadata;
    }

    /**
     * Set the metadata associated with the decision.
     *
     * @param  array<string, mixed>  $metadata
     */
    public function setMetadata(array $metadata): void
    {
        $this->metadata = $metadata;
    }

    /**
     * Add a metadata entry to the decision.
     */
    public function addMetadata(string $key, mixed $value): void
    {
        $this->metadata[$key] = $value;
    }

    /**
     * Get the list of event class associated with the decision.
     *
     * @return array<int, TEvent>
     */
    public function events(): array
    {
        return $this->events;
    }

    /**
     * Set the list of event class associated with the decision.
     *
     * @param  array<int, TEvent>  $events
     */
    public function setEvents(array $events): void
    {
        $this->events = $events;
    }

    /**
     * Add an event class to the decision.
     *
     * * @param  TEvent  $event
     */
    public function addEvent($event): void
    {
        $this->events[] = $event;
    }

    /**
     * Get the next state associated with the decision.
     *
     * * @return TState
     */
    public function nextState()
    {
        return $this->nextState;
    }
}
