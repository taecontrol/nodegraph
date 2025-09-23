<?php

// config for Taecontrol/NodeGraph
return [
    'graphs' => [
        [
            /**
             * The name of the default graph. You can have multiple graphs if you want.
             */
            'name' => 'default',
            /**
             * The enum class that represents the states of the graph.
             */
            'state_enum' => "App\Domain\Agent\YourStateEnum::class",
        ],
    ],
];
