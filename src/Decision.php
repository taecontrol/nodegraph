<?php

namespace Taecontrol\NodeGraph;

abstract class Decision implements Contracts\HasMetadata
{
    /**
     * @var array <string, mixed> Metadata associated with the decision.
     */
    protected array $metadata = [];

    /**
     * @var array<int, Event> List of event class associated with the decision.
     */
    protected array $events = [];

    public function metadata(): array
    {
        return $this->metadata;
    }

    public function setMetadata(array $metadata): void
    {
        $this->metadata = $metadata;
    }

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
}