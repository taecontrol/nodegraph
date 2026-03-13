<?php

namespace Taecontrol\NodeGraph\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Taecontrol\NodeGraph\Models\Thread;

class ThreadFactory extends Factory
{
    protected $model = Thread::class;

    public function definition()
    {
        return [
            'threadable_type' => 'test',
            'threadable_id' => fake()->uuid(),
            'graph_name' => 'default',
            'metadata' => [],
        ];
    }

    public function started($state): static
    {
        return $this->state(fn () => [
            'current_state' => $state,
            'started_at' => now(),
        ]);
    }

    public function uninitialized(): static
    {
        return $this->state(fn () => [
            'current_state' => null,
            'started_at' => null,
        ]);
    }
}
