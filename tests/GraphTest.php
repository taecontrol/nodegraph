<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Taecontrol\NodeGraph\Database\Factories\ThreadFactory;
use Taecontrol\NodeGraph\Models\Thread;
use Taecontrol\NodeGraph\Tests\Fixtures\SampleState;
use Taecontrol\NodeGraph\Tests\Fixtures\TestContext;
use Taecontrol\NodeGraph\Events\GraphFinished;
use Taecontrol\NodeGraph\Tests\Fixtures\TestEvent;
use Taecontrol\NodeGraph\Tests\Fixtures\TestGraph;
use Taecontrol\NodeGraph\Tests\Fixtures\BadTransitionGraph;
use Taecontrol\NodeGraph\Tests\Fixtures\BadTransitionState;
use Taecontrol\NodeGraph\Tests\Fixtures\SelfEdgeGraph;
use Taecontrol\NodeGraph\Tests\Fixtures\SelfEdgeState;
use Taecontrol\NodeGraph\Exceptions\InvalidStateTransition;

beforeEach(function () {
    config()->set('nodegraph.graphs', [
        [
            'name' => 'default',
            'state_enum' => SampleState::class,
        ],
    ]);
});

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
    $graph->assertValidTransition(SampleState::Start, SampleState::Middle);

    expect(fn () => $graph->assertValidTransition(SampleState::Start, SampleState::End))
        ->toThrow(InvalidStateTransition::class);
});

it('runs and advances thread state, creating metadata and checkpoints', function () {
    $graph = new TestGraph;
    /** @var Thread $thread */
    $thread = ThreadFactory::new()->create();
    $context = new TestContext($thread);

    Event::fake();

    // First run: initialize and move Start -> Middle
    $graph->run($context);

    $thread->refresh();
    expect($thread->current_state)->toBe(SampleState::Middle);
    expect($thread->started_at)->not->toBeNull();
    expect($thread->finished_at)->toBeNull();

    expect($thread->metadata)->toHaveKey('start');
    expect($thread->metadata['start'])->toHaveKey('from');
    expect($thread->metadata['start']['from'])->toBe('start');
    expect($thread->metadata['start'])->toHaveKey('execution_time');

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
    expect($thread->finished_at)->not->toBeNull();

    $cp2 = $thread->checkpoints()->latest('id')->first();
    expect($cp2->state)->toBe(SampleState::End);

    Event::assertDispatched(function (TestEvent $event) {
        return $event->name === 'middle';
    });
    Event::assertDispatched(GraphFinished::class);

    // Third run: End is terminal, run() is a no-op
    Event::fake();
    $checkpointCountBefore = $thread->checkpoints()->count();
    $metadataBefore = $thread->metadata;

    $graph->run($context);

    $thread->refresh();
    expect($thread->current_state)->toBe(SampleState::End);
    expect($thread->checkpoints()->count())->toBe($checkpointCountBefore);
    expect($thread->metadata)->toBe($metadataBefore);
    Event::assertNotDispatched(TestEvent::class);
});

it('does not execute node when state is terminal', function () {
    $graph = new TestGraph;
    $thread = ThreadFactory::new()->started(SampleState::End)->create();
    $context = new TestContext($thread);

    Event::fake();

    $graph->run($context);

    $thread->refresh();
    expect($thread->current_state)->toBe(SampleState::End);
    expect($thread->checkpoints()->count())->toBe(0);
    Event::assertNotDispatched(TestEvent::class);
});

it('remains inert across multiple run calls on terminal state', function () {
    $graph = new TestGraph;
    $thread = ThreadFactory::new()->started(SampleState::End)->create();
    $context = new TestContext($thread);

    Event::fake();

    $graph->run($context);
    $graph->run($context);
    $graph->run($context);

    $thread->refresh();
    expect($thread->current_state)->toBe(SampleState::End);
    expect($thread->checkpoints()->count())->toBe(0);
    Event::assertNotDispatched(TestEvent::class);
});

it('treats self-edge state as non-terminal and executes normally', function () {
    config()->set('nodegraph.graphs', [
        [
            'name' => 'default',
            'state_enum' => SelfEdgeState::class,
        ],
    ]);

    $graph = new SelfEdgeGraph;

    expect($graph->isTerminal(SelfEdgeState::Processing))->toBeFalse();
    expect($graph->isTerminal(SelfEdgeState::Done))->toBeTrue();

    $thread = ThreadFactory::new()->started(SelfEdgeState::Processing)->create();
    $context = new TestContext($thread);

    Event::fake();

    $graph->run($context);

    $thread->refresh();
    expect($thread->current_state)->toBe(SelfEdgeState::Done);
    expect($thread->checkpoints()->count())->toBe(1);
    expect($thread->finished_at)->not->toBeNull();
    Event::assertDispatched(GraphFinished::class);
});

it('throws before side effects on invalid transition', function () {
    config()->set('nodegraph.graphs', [
        [
            'name' => 'default',
            'state_enum' => BadTransitionState::class,
        ],
    ]);

    $graph = new BadTransitionGraph;
    $thread = ThreadFactory::new()->started(BadTransitionState::A)->create();
    $context = new TestContext($thread);

    Event::fake();

    expect(fn () => $graph->run($context))
        ->toThrow(InvalidStateTransition::class);

    $thread->refresh();
    expect($thread->current_state)->toBe(BadTransitionState::A);
    expect($thread->checkpoints()->count())->toBe(0);
    Event::assertNotDispatched(TestEvent::class);
});

it('sets finished_at when transitioning to terminal state', function () {
    $graph = new TestGraph;
    $thread = ThreadFactory::new()->started(SampleState::Middle)->create();
    $context = new TestContext($thread);

    Event::fake();

    $graph->run($context);

    $thread->refresh();
    expect($thread->current_state)->toBe(SampleState::End);
    expect($thread->finished_at)->not->toBeNull();
    expect($thread->finished_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

it('dispatches GraphFinished with correct payload', function () {
    $graph = new TestGraph;
    $thread = ThreadFactory::new()->started(SampleState::Middle)->create();
    $context = new TestContext($thread);

    Event::fake();

    $graph->run($context);

    Event::assertDispatched(function (GraphFinished $event) use ($thread) {
        return $event->thread->id === $thread->id
            && $event->graphName === 'default'
            && $event->finalState === SampleState::End;
    });
});

it('rolls back all changes when createCheckpoint throws', function () {
    $graph = new class extends TestGraph
    {
        protected function createCheckpoint($thread, $decision): void
        {
            throw new \RuntimeException('checkpoint failed');
        }
    };

    $thread = ThreadFactory::new()->started(SampleState::Start)->create();
    $context = new TestContext($thread);

    expect(fn () => $graph->run($context))->toThrow(\RuntimeException::class, 'checkpoint failed');

    $thread->refresh();
    expect($thread->current_state)->toBe(SampleState::Start);
    expect($thread->checkpoints()->count())->toBe(0);
});

it('rolls back initialization when failure on null-state thread', function () {
    $graph = new class extends TestGraph
    {
        protected function createCheckpoint($thread, $decision): void
        {
            throw new \RuntimeException('checkpoint failed');
        }
    };

    $thread = ThreadFactory::new()->uninitialized()->create();
    $context = new TestContext($thread);

    expect(fn () => $graph->run($context))->toThrow(\RuntimeException::class, 'checkpoint failed');

    $thread->refresh();
    expect($thread->current_state)->toBeNull();
    expect($thread->started_at)->toBeNull();
});

it('rolls back terminal transition on failure', function () {
    $graph = new class extends TestGraph
    {
        protected function createCheckpoint($thread, $decision): void
        {
            throw new \RuntimeException('checkpoint failed');
        }
    };

    $thread = ThreadFactory::new()->started(SampleState::Middle)->create();
    $context = new TestContext($thread);

    expect(fn () => $graph->run($context))->toThrow(\RuntimeException::class, 'checkpoint failed');

    $thread->refresh();
    expect($thread->current_state)->toBe(SampleState::Middle);
    expect($thread->finished_at)->toBeNull();
    expect($thread->checkpoints()->count())->toBe(0);
});

it('does not dispatch events on rollback', function () {
    Event::fake();

    $graph = new class extends TestGraph
    {
        protected function createCheckpoint($thread, $decision): void
        {
            throw new \RuntimeException('checkpoint failed');
        }
    };

    $thread = ThreadFactory::new()->started(SampleState::Middle)->create();
    $context = new TestContext($thread);

    try {
        $graph->run($context);
    } catch (\RuntimeException) {
    }

    Event::assertNotDispatched(TestEvent::class);
    Event::assertNotDispatched(GraphFinished::class);
});

it('is retryable after rollback', function () {
    $failOnce = new class
    {
        public static bool $shouldFail = true;
    };

    $graph = new class($failOnce) extends TestGraph
    {
        public function __construct(private object $flag)
        {
            parent::__construct();
        }

        protected function createCheckpoint($thread, $decision): void
        {
            if ($this->flag::$shouldFail) {
                $this->flag::$shouldFail = false;

                throw new \RuntimeException('checkpoint failed');
            }

            parent::createCheckpoint($thread, $decision);
        }
    };

    $thread = ThreadFactory::new()->started(SampleState::Start)->create();
    $context = new TestContext($thread);

    // First run fails
    expect(fn () => $graph->run($context))->toThrow(\RuntimeException::class);
    $thread->refresh();
    expect($thread->current_state)->toBe(SampleState::Start);

    // Second run succeeds
    $graph->run($context);
    $thread->refresh();
    expect($thread->current_state)->toBe(SampleState::Middle);
    expect($thread->checkpoints()->count())->toBe(1);
});

it('works inside an outer transaction (savepoints)', function () {
    DB::beginTransaction();

    $graph = new TestGraph;
    $thread = ThreadFactory::new()->started(SampleState::Start)->create();
    $context = new TestContext($thread);

    Event::fake();
    $graph->run($context);

    DB::commit();

    $thread->refresh();
    expect($thread->current_state)->toBe(SampleState::Middle);
    expect($thread->checkpoints()->count())->toBe(1);
    Event::assertDispatched(TestEvent::class);
});

it('outer transaction rollback undoes graph run', function () {
    $graph = new TestGraph;
    $thread = ThreadFactory::new()->started(SampleState::Start)->create();
    $context = new TestContext($thread);

    DB::beginTransaction();

    Event::fake();
    $graph->run($context);

    DB::rollBack();

    $thread->refresh();
    expect($thread->current_state)->toBe(SampleState::Start);
    expect($thread->checkpoints()->count())->toBe(0);
});
