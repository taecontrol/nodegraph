# Build agentic apps with NodeGraph

[![Latest Version on Packagist](https://img.shields.io/packagist/v/taecontrol/nodegraph.svg?style=flat-square)](https://packagist.org/packages/taecontrol/nodegraph)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/taecontrol/nodegraph/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/taecontrol/nodegraph/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/taecontrol/nodegraph/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/taecontrol/nodegraph/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/taecontrol/nodegraph.svg?style=flat-square)](https://packagist.org/packages/taecontrol/nodegraph)

NodeGraph is a tiny, testable state-graph runtime for Laravel. Define your process as an enum of states, map each state to a Node class, and let a Graph run the flow step-by-step while recording checkpoints, metadata, and dispatching events.

Core capabilities:
- Deterministic state transitions via a directed graph
- Nodes execute your domain logic and return a Decision (next state, metadata, events)
- Threads persist progress (`graph_name`, `current_state`, timestamps, metadata)
- Checkpoints store a timeline of transitions with merged metadata
- Multi-graph: configure multiple independent graphs, each with its own state enum

## Why multi-graph?
Real systems rarely have a single lifecycle. Orders, shipments, payouts, document reviews—each has its own progression logic. Multi-graph support lets you:
- Model each lifecycle with a dedicated state enum + Graph class
- Persist them all in the same `threads` table (distinguished by `graph_name`)
- Keep logic isolated while sharing infrastructure (events, metadata, checkpoints)

## Requirements
- PHP >= 8.4
- Laravel (Illuminate Contracts) ^12.0 (works with ^11.0 as well per constraint, but docs target 12)

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

Locate `config/nodegraph.php`. You will see a `graphs` array. Each entry declares a graph name and the enum that represents its states.

Single-graph (default) usage example (Quickstart below shows code usage):

```php
return [
    'graphs' => [
        [
            'name' => 'default',
            'state_enum' => \App\Domain\Order\OrderState::class, // IMPORTANT: class constant (no quotes)
        ],
    ],
];
```

> Note: The published config may show a quoted "::class" string placeholder—replace it with the actual class constant as shown above.

## Core concepts
- State enum: a PHP BackedEnum implementing `Taecontrol\NodeGraph\Contracts\HasNode`. Each enum case maps to a Node class.
- Node: extends `Taecontrol\NodeGraph\Node`. Implement `handle($context)` and return a `Decision`.
- Decision: extends `Taecontrol\NodeGraph\Decision`. Holds `nextState()`, `metadata()`, and `events()`.
- Graph: extends `Taecontrol\NodeGraph\Graph`. Implement `define()` (edges) and `initialState()`.
- Context: extends `Taecontrol\NodeGraph\Context`. Provides a `thread()` method.
- Thread model: `Taecontrol\NodeGraph\Models\Thread` stores `graph_name`, `current_state`, `metadata`, `started_at`, `finished_at`; has many `checkpoints`.
- Checkpoint model: `Taecontrol\NodeGraph\Models\Checkpoint` stores `state` + snapshot metadata per run.

## How it runs
`Graph::run($context)` will:
1. Initialize the thread's `current_state` to the graph's `initialState()` if null, setting `started_at`.
2. If the current state is terminal (no outgoing edges), return immediately — no node is executed.
3. Resolve the Node for the current state and execute it.
4. Validate the transition (`assertValidTransition`) before any side effects. Throws `InvalidStateTransition` if the node returns an undeclared next state.
5. The Node's Decision metadata is augmented with `state` and `execution_time` (seconds, float).
6. Inside a database transaction (with `lockForUpdate()` on existing threads):
   - Thread metadata is merged under the key of the current state's enum value.
   - A checkpoint is created with merged metadata.
   - Thread state advances. If the new state is terminal, `finished_at` is set.
7. After the transaction commits, Decision events are dispatched. If the new state is terminal, a `GraphFinished` event is dispatched.

All database writes in step 6 are atomic — they succeed together or roll back together. Events in step 7 only fire after a successful commit, so listeners always see consistent data.

## Quickstart (single graph)

1) Create a state enum mapping states to Node classes:

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

// DoneNode is never executed — terminal states are no-ops.
// The enum still maps Done to a node class (PHP requires exhaustive match),
// but Graph::run() returns early before resolving it.
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
        // Done has no outgoing edges; it's terminal
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

6) Create and run a Thread (e.g. controller, job, listener):

```php
use Taecontrol\NodeGraph\Models\Thread;

$thread = Thread::create([
    'threadable_type' => \App\Models\Order::class,
    'threadable_id' => (string) \Illuminate\Support\Str::ulid(),
    'graph_name' => 'default', // single-graph setup uses 'default'
    'metadata' => [],
]);

$context = new \App\Contexts\OrderContext($thread);
$graph = app(\App\Graphs\OrderGraph::class); // graph_name does NOT auto-resolve to a class

$graph->run($context); // Start -> Charge
$graph->run($context); // Charge -> Done (finished_at set, GraphFinished dispatched)
$graph->run($context); // Done is terminal — no-op
```

Observability:
- `threads.current_state` moves across runs
- `threads.metadata` accumulates per-state metadata (includes `execution_time`)
- `checkpoints` appended each run with merged metadata snapshot
- Domain events dispatched through Laravel's event dispatcher

## Advanced: Multi-graph usage
You can define multiple graphs—each with its own enum—inside the same application. All share `threads` and `checkpoints` tables, distinguished by `graph_name`.

`config/nodegraph.php` example:

```php
return [
    'graphs' => [
        [
            'name' => 'default',
            'state_enum' => \App\Domain\Order\OrderState::class,
        ],
        [
            'name' => 'shipment',
            'state_enum' => \App\Domain\Shipment\ShipmentState::class,
        ],
    ],
];
```

Second enum + graph example:

```php
use Taecontrol\NodeGraph\Contracts\HasNode;

enum ShipmentState: string implements HasNode
{
    case Queued = 'queued';
    case Picking = 'picking';
    case Dispatching = 'dispatching';
    case Delivered = 'delivered';

    public function node(): string
    {
        return match ($this) {
            self::Queued => \App\Nodes\Shipment\QueuedNode::class,
            self::Picking => \App\Nodes\Shipment\PickingNode::class,
            self::Dispatching => \App\Nodes\Shipment\DispatchingNode::class,
            self::Delivered => \App\Nodes\Shipment\DeliveredNode::class,
        };
    }
}

class ShipmentGraph extends \Taecontrol\NodeGraph\Graph
{
    public function define(): void
    {
        $this->addEdge(ShipmentState::Queued, ShipmentState::Picking);
        $this->addEdge(ShipmentState::Picking, ShipmentState::Dispatching);
        $this->addEdge(ShipmentState::Dispatching, ShipmentState::Delivered);
    }

    public function initialState(): ShipmentState
    {
        return ShipmentState::Queued;
    }
}
```

Creating threads for different graphs:

```php
$orderThread = Thread::create([
    'threadable_type' => \App\Models\Order::class,
    'threadable_id' => (string) \Illuminate\Support\Str::ulid(),
    'graph_name' => 'default',
]);

$shipmentThread = Thread::create([
    'threadable_type' => \App\Models\Shipment::class,
    'threadable_id' => (string) \Illuminate\Support\Str::ulid(),
    'graph_name' => 'shipment',
]);

app(\App\Graphs\OrderGraph::class)->run(new OrderContext($orderThread));
app(\App\Graphs\ShipmentGraph::class)->run(new ShipmentContext($shipmentThread));
```

### Important notes
- `graph_name` does NOT auto-resolve a Graph class—you must choose the appropriate class yourself (e.g. via a map or conditional lookup).
- Each thread's state casting uses the enum from the matching config entry. If the `graph_name` is not configured, the enum cast will not apply (state behaves as a raw string). Document or validate `graph_name` creation to avoid surprises.
- Metadata and events are entirely isolated per thread—even across different graphs.
- `finished_at` is set automatically when a thread transitions into a terminal state. A `GraphFinished` event is dispatched at the same time.

### Retrieving enum metadata dynamically
If you need the enum class for a given thread:
```php
$enumClass = collect(config('nodegraph.graphs'))
    ->firstWhere('name', $thread->graph_name)['state_enum'] ?? null;
```
Check for `null` if the graph might not be configured.

## API cheatsheet
- `Graph::addEdge(From, To)` — define allowed transitions
- `Graph::neighbors(State): array` — list next states
- `Graph::canTransition(From, To): bool` — check if transition is allowed
- `Graph::assertValidTransition(From, To): void` — throws `InvalidStateTransition` on invalid transitions
- `Graph::isTerminal(State): bool` — true when no outgoing edges
- `Graph::run(Context): void` — execute one step and persist side effects
- `GraphFinished` event — dispatched when a thread reaches a terminal state (carries `thread`, `graphName`, `finalState`)

## Data model
Tables (published migration):

- threads
  - id (ULID), threadable_type, threadable_id (morphs)
  - graph_name (string)
  - current_state (string, cast to enum when configured), metadata (json)
  - started_at, finished_at, timestamps, softDeletes
- checkpoints
  - id (ULID), thread_id, state (string, cast to enum when configured)
  - metadata (json), timestamps, softDeletes

Both `Thread::current_state` and `Checkpoint::state` are cast using the selected graph's `state_enum` if a matching `graph_name` is found.

## Behavior with unknown graph_name
If a thread references a `graph_name` absent from configuration:
- No enum casting will occur (raw string states)
- You must handle validation manually
- Graph execution will still function if you manually run the appropriate Graph class with states using the same raw values

## Testing

```bash
composer test
```

## Changelog
Please see [CHANGELOG](CHANGELOG.md) for recent changes.

## Contributing
Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits
- [Luis Güette](https://github.com/guetteman)
- [All Contributors](../../contributors)

## License
The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
