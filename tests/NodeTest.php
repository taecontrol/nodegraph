<?php

use Taecontrol\NodeGraph\Database\Factories\ThreadFactory;
use Taecontrol\NodeGraph\Models\Thread;
use Taecontrol\NodeGraph\Node;
use Taecontrol\NodeGraph\Tests\Fixtures\SampleState;
use Taecontrol\NodeGraph\Tests\Fixtures\SimpleDecision;
use Taecontrol\NodeGraph\Tests\Fixtures\StartNode;
use Taecontrol\NodeGraph\Tests\Fixtures\TestContext;

beforeEach(function () {
    config()->set('nodegraph.graphs', [
        [
            'name' => 'default',
            'state_enum' => SampleState::class,
        ],
    ]);
});

it('adds state and execution_time metadata when executing a node', closure: function () {
    /** @var Thread $thread */
    $thread = ThreadFactory::new()->create();
    $thread->current_state = SampleState::Start;
    $thread->save();

    $context = new TestContext($thread);

    $node = new StartNode;
    $decision = $node->execute($context);

    expect($decision)->toBeInstanceOf(SimpleDecision::class);

    $metadata = $decision->metadata();

    expect($metadata)->toHaveKey('from');
    expect($metadata['from'])->toBe('start');

    expect($metadata)->toHaveKey('state');
    expect($metadata['state'])->toBe(SampleState::Start);

    expect($metadata)->toHaveKey('execution_time');
    expect($metadata['execution_time'])->toBeFloat();
    expect($metadata['execution_time'])->toBeGreaterThanOrEqual(0.0);
});

it('returns the same Decision instance produced by handle (no cloning) and augments its metadata', function () {
    $thread = ThreadFactory::new()->create();
    $thread->current_state = SampleState::Middle;
    $thread->save();

    $context = new TestContext($thread);

    $original = new SimpleDecision(SampleState::End);

    $node = new class($original) extends Node
    {
        public function __construct(private SimpleDecision $decision) {}

        public function handle($data): SimpleDecision
        {
            return $this->decision;
        }
    };

    $result = $node->execute($context);

    // Same instance
    expect($result)->toBe($original);

    // Metadata was augmented in-place
    $metadata = $original->metadata();
    expect($metadata)->toHaveKey('state');
    expect($metadata['state'])->toBe(SampleState::Middle);
    expect($metadata)->toHaveKey('execution_time');
});

it('handles null current_state by setting state metadata to null', function () {
    $thread = ThreadFactory::new()->create(); // current_state stays null

    $context = new TestContext($thread);

    $node = new StartNode;
    $decision = $node->execute($context);

    $metadata = $decision->metadata();

    expect($metadata)->toHaveKey('state');
    expect($metadata['state'])->toBeNull();
    expect($metadata)->toHaveKey('execution_time');
});
