<?php

namespace Taecontrol\NodeGraph\Exceptions;

use BackedEnum;
use RuntimeException;

class InvalidStateTransition extends RuntimeException
{
    public function __construct(
        public readonly BackedEnum $sourceState,
        public readonly BackedEnum $targetState,
        public readonly array $allowedTransitions = [],
    ) {
        $allowed = implode(', ', array_map(
            fn (BackedEnum $s) => $s->value,
            $allowedTransitions,
        ));

        parent::__construct(
            "Invalid state transition from [{$sourceState->value}] to [{$targetState->value}]."
            .($allowed ? " Allowed transitions from [{$sourceState->value}]: [{$allowed}]." : ''),
        );
    }

    /**
     * Get the exception's context information for logging.
     *
     * @return array<string, mixed>
     */
    public function context(): array
    {
        return [
            'source_state'        => $this->sourceState->value,
            'target_state'        => $this->targetState->value,
            'allowed_transitions' => array_map(fn (BackedEnum $s) => $s->value, $this->allowedTransitions),
        ];
    }
}
