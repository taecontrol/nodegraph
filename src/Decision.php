<?php

namespace Taecontrol\NodeGraph;

abstract class Decision implements Contracts\HasMetadata
{
    protected array $metadata = [];

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
}