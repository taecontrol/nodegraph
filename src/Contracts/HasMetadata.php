<?php

namespace Taecontrol\NodeGraph\Contracts;

/**
 * Interface HasNode
 */
interface HasMetadata
{
    /**
     * Get the metadata.
     */
    public function metadata(): array;

    /**
     * Set the metadata.
     */
    public function setMetadata(array $metadata): void;

    /**
     * Add a metadata entry.
     */
    public function addMetadata(string $key, mixed $value): void;
}
