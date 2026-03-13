<?php

namespace Taecontrol\NodeGraph\Exceptions;

use BackedEnum;
use RuntimeException;

class InvalidStateTransition extends RuntimeException
{
    public function __construct(
        public readonly BackedEnum $from,
        public readonly BackedEnum $to,
        public readonly array $allowedTransitions = [],
    ) {
        $allowed = implode(', ', array_map(
            fn (BackedEnum $s) => $s->value,
            $allowedTransitions,
        ));

        parent::__construct(
            "Invalid state transition from [{$from->value}] to [{$to->value}]."
            .($allowed ? " Allowed transitions from [{$from->value}]: [{$allowed}]." : ''),
        );
    }
}
