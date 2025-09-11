<?php

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Taecontrol\NodeGraph\Models\Thread;
use Taecontrol\NodeGraph\Tests\Fixtures\SampleState;
use Taecontrol\NodeGraph\Tests\Fixtures\TestContext;
use Taecontrol\NodeGraph\Tests\Fixtures\TestEvent;
use Taecontrol\NodeGraph\Tests\Fixtures\TestGraph;

function makeThread(): Thread
{
    return Thread::create([
        'threadable_type' => 'test',
        'threadable_id' => (string) Str::ulid(),
        'metadata' => [],
    ]);
}

it('defines neighbors and terminal states correctly', function () {
    $graph = new TestGraph;

    expect($graph->neighbors(SampleState::Start))->toEqual([SampleState::Middle]);
    expect($graph->neighbors(SampleState::Middle))->toEqual([SampleState::End]);
    expect($graph->neighbors(SampleState::End))->toEqual([]);

    expect($graph->canTransition(SampleState::Start, SampleState::Middle))->toBeTrue();
    expect($graph->canTransition(SampleState::Start, SampleState::End))->toBeFalse();

    expect($graph->isTerminal(SampleState::End))->toBeTrue();
    expect($graph->isTerminal(SampleState::Start))->toBeFalse();
});

it('asserts invalid transitions', function () {
    $graph = new TestGraph;
    $graph->assert(SampleState::Start, SampleState::Middle); // does not throw

    expect(fn () => $graph->assert(SampleState::Start, SampleState::End))
        ->toThrow(InvalidArgumentException::class);
});

it('runs and advances thread state, creating metadata and checkpoints', function () {
    $graph = new TestGraph;
    $thread = makeThread();
    $context = new TestContext($thread);

    Event::fake();

    // First run: initialize and move Start -> Middle
    $graph->run($context);

    $thread->refresh();
    expect($thread->current_state)->toBe(SampleState::Middle);
    expect($thread->started_at)->not->toBeNull();
    expect($thread->finished_at)->toBeNull();

    // Metadata stored under 'start' with at least 'from' and 'execution_time'
    expect($thread->metadata)->toHaveKey('start');
    expect($thread->metadata['start'])->toHaveKey('from');
    expect($thread->metadata['start']['from'])->toBe('start');
    expect($thread->metadata['start'])->toHaveKey('execution_time');

    // A checkpoint to Middle with merged metadata
    $cp1 = $thread->checkpoints()->latest('id')->first();
    expect($cp1->state)->toBe(SampleState::Middle);
    expect($cp1->metadata)->toBeArray();
    expect($cp1->metadata)->toHaveKey('start');

    Event::assertDispatched(function (TestEvent $event) {
        return $event->name === 'start';
    });

    // Second run: Middle -> End
    Event::fake();
    $graph->run($context);

    $thread->refresh();
    expect($thread->current_state)->toBe(SampleState::End);
    expect($thread->finished_at)->toBeNull(); // per current implementation

    $cp2 = $thread->checkpoints()->latest('id')->first();
    expect($cp2->state)->toBe(SampleState::End);

    Event::assertDispatched(function (TestEvent $event) {
        return $event->name === 'middle';
    });

    // Third run: End is terminal, state remains End, still creates a checkpoint
    Event::fake();
    $graph->run($context);

    $thread->refresh();
    expect($thread->current_state)->toBe(SampleState::End);

    $cp3 = $thread->checkpoints()->latest('id')->first();
    expect($cp3->state)->toBe(SampleState::End);

    Event::assertDispatched(function (TestEvent $event) {
        return $event->name === 'end';
    });
});
