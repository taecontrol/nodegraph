<?php

namespace Taecontrol\NodeGraph;

use BackedEnum;
use Taecontrol\NodeGraph\Contracts\HasNode;

abstract class Decision implements Contracts\HasMetadata
{
    public function __construct(
        protected BackedEnum&HasNode $nextState,
        /** @var array<string, mixed> */
        protected array $metadata = [],
        /** @var array<int, Event> */
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
     * @return array<int, Event>
     */
    public function events(): array
    {
        return $this->events;
    }

    /**
     * Set the list of event class associated with the decision.
     *
     * @param  array<int, Event>  $events
     */
    public function setEvents(array $events): void
    {
        $this->events = $events;
    }

    /**
     * Add an event class to the decision.
     */
    public function addEvent(Event $event): void
    {
        $this->events[] = $event;
    }

    /**
     * Get the next state associated with the decision.
     */
    public function nextState(): BackedEnum&HasNode
    {
        return $this->nextState;
    }
}
