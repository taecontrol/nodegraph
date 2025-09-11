<?php

namespace Taecontrol\NodeGraph\Contracts;

/**
 * Interface HasMetadata
 */
interface HasMetadata
{
    /**
     * Get the metadata.
     *
     * @return array<string, mixed>
     */
    public function metadata(): array;

    /**
     * Set the metadata.
     *
     * @param  array<string, mixed>  $metadata
     */
    public function setMetadata(array $metadata): void;

    /**
     * Add a metadata entry.
     */
    public function addMetadata(string $key, mixed $value): void;
}
