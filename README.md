# Build agentic apps with NodeGraph

[![Latest Version on Packagist](https://img.shields.io/packagist/v/taecontrol/nodegraph.svg?style=flat-square)](https://packagist.org/packages/taecontrol/nodegraph)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/taecontrol/nodegraph/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/taecontrol/nodegraph/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/taecontrol/nodegraph/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/taecontrol/nodegraph/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/taecontrol/nodegraph.svg?style=flat-square)](https://packagist.org/packages/taecontrol/nodegraph)

NodeGraph is a tiny, testable state-graph runtime for Laravel. Define your process as an enum of states, wire each state to a Node class, and let a Graph run the flow step-by-step while recording checkpoints, metadata, and dispatching events.

- Deterministic state transitions via a directed graph
- Nodes execute your domain logic and return a Decision (next state, metadata, events)
- Threads persist progress (current_state, started_at/finished_at, metadata)
- Checkpoints store a timeline of transitions with merged metadata

## Installation

Install via Composer:

```bash
composer require taecontrol/nodegraph
```

Publish the migration and migrate:

```bash
php artisan vendor:publish --tag="nodegraph-migrations"
php artisan migrate
```

Publish the config:

```bash
php artisan vendor:publish --tag="nodegraph-config"
```

Then set the enum class used to cast the `current_state` and checkpoint `state` fields. In `config/nodegraph.php`:

```php
return [
    // IMPORTANT: use the class constant (no quotes)
    'state_enum' => \App\Domain\Agent\YourStateEnum::class,
];
```

## Core concepts

- State enum: a PHP BackedEnum that implements `Taecontrol\NodeGraph\Contracts\HasNode`. Each enum case maps to a Node class.
- Node: extends `Taecontrol\NodeGraph\Node`. Implement `handle($context)` and return a `Decision`.
- Decision: extends `Taecontrol\NodeGraph\Decision`. Holds `nextState()`, `metadata()`, and `events()`.
- Graph: extends `Taecontrol\NodeGraph\Graph`. Implement `define()` to add edges and `initialState()`.
- Context: extends `Taecontrol\NodeGraph\Context`. Provides a `thread()` method.
- Thread model: `Taecontrol\NodeGraph\Models\Thread` stores `current_state`, `metadata`, `started_at`, `finished_at` and has many `checkpoints`.
- Checkpoint model: `Taecontrol\NodeGraph\Models\Checkpoint` stores `state` and `metadata` snapshots.

## How it runs

When you call `Graph::run($context)`:

1) If the thread has no `current_state`, it's set to `initialState()` and `started_at` is recorded.
2) The Node for the current state is resolved from the container and executed.
3) The Node returns a Decision. Execution time and current state are automatically added to Decision metadata.
4) Thread metadata is merged under the current state's key, a Checkpoint is created with merged metadata, and Decision events are dispatched.
5) If allowed by the graph edges, the thread advances to the Decision's `nextState()`; otherwise it remains in place. On a subsequent run when at a terminal state (no outgoing edges), `finished_at` is set.

## Quickstart

1) Create a state enum that maps states to Node classes:

```php
use Taecontrol\NodeGraph\Contracts\HasNode;

enum OrderState: string implements HasNode
{
    case Start = 'start';
    case Charge = 'charge';
    case Done = 'done';

    public function node(): string
    {
        return match ($this) {
            self::Start => \App\Nodes\StartNode::class,
            self::Charge => \App\Nodes\ChargeNode::class,
            self::Done => \App\Nodes\DoneNode::class,
        };
    }
}
```

2) Create a Decision class:

```php
namespace App\Decisions;

use Taecontrol\NodeGraph\Decision;

class SimpleDecision extends Decision {}
```

3) Create Nodes for each state:

```php
namespace App\Nodes;

use App\Decisions\SimpleDecision;
use App\Enums\OrderState;
use App\Events\OrderEvent; // extends Taecontrol\NodeGraph\Event
use Taecontrol\NodeGraph\Node;

class StartNode extends Node
{
    public function handle($context): SimpleDecision
    {
        $d = new SimpleDecision(OrderState::Charge);
        $d->addMetadata('from', 'start');
        $d->addEvent(new OrderEvent('start'));
        return $d;
    }
}

class ChargeNode extends Node
{
    public function handle($context): SimpleDecision
    {
        // ... charge logic ...
        $d = new SimpleDecision(OrderState::Done);
        $d->addMetadata('from', 'charge');
        $d->addEvent(new OrderEvent('charged'));
        return $d;
    }
}

class DoneNode extends Node
{
    public function handle($context): SimpleDecision
    {
        $d = new SimpleDecision(null); // stay in terminal state
        $d->addMetadata('from', 'done');
        $d->addEvent(new OrderEvent('done'));
        return $d;
    }
}
```

4) Define your Graph:

```php
use Taecontrol\NodeGraph\Graph;
use App\Enums\OrderState;

class OrderGraph extends Graph
{
    public function define(): void
    {
        $this->addEdge(OrderState::Start, OrderState::Charge);
        $this->addEdge(OrderState::Charge, OrderState::Done);
        // Done has no outgoing edges, so it's terminal
    }

    public function initialState(): OrderState
    {
        return OrderState::Start;
    }
}
```

5) Provide a Context that exposes the Thread:

```php
use Taecontrol\NodeGraph\Context;
use Taecontrol\NodeGraph\Models\Thread;

class OrderContext extends Context
{
    public function __construct(protected Thread $thread) {}

    public function thread(): Thread
    {
        return $this->thread;
    }
}
```

6) Create and run a Thread (e.g. from a controller, job, or listener):

```php
use Taecontrol\NodeGraph\Models\Thread;

$thread = Thread::create([
    'threadable_type' => \App\Models\Order::class, // anything morphable
    'threadable_id' => (string) \Illuminate\Support\Str::ulid(),
    'metadata' => [],
]);

$context = new \App\Contexts\OrderContext($thread);
$graph = app(\App\Graphs\OrderGraph::class);

$graph->run($context); // Start -> Charge
$graph->run($context); // Charge -> Done
$graph->run($context); // Done is terminal; finished_at will be set on this run
```

What you get:

- `threads.current_state` advances across runs; `started_at/finished_at` are set.
- `threads.metadata` accumulates per-state metadata, including `execution_time`.
- `checkpoints` are appended each run with merged metadata.
- Your `OrderEvent` instances are dispatched via Laravel's `event()` helper.

## API cheatsheet

- `Graph::addEdge(From, To)` — define allowed transitions.
- `Graph::neighbors(State): array` — list next states.
- `Graph::canTransition(From, To): bool` — validate a transition.
- `Graph::assert(From, To): void` — throws on invalid transitions.
- `Graph::isTerminal(State): bool` — true when a state has no outgoing edges.
- `Graph::run(Context): void` — runs one step and persists side effects.

## Data model

This package ships two tables (via the publishable migration):

- threads
  - id (ULID), threadable_type, threadable_id (morphs)
  - current_state (string, cast to your enum), metadata (json)
  - started_at, finished_at, timestamps, softDeletes
- checkpoints
  - id (ULID), thread_id, state (string, cast to your enum)
  - metadata (json), timestamps, softDeletes

Both `Thread::current_state` and `Checkpoint::state` are cast using your `state_enum` config.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [Luis Güette](https://github.com/guetteman)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
