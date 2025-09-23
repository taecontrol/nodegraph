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
}
