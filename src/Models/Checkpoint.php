<?php

namespace Taecontrol\NodeGraph\Models;

use BackedEnum;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Taecontrol\NodeGraph\Contracts\HasNode;

/**
 * @property (BackedEnum&HasNode)|null $state
 * @property array<string, mixed>|null $metadata
 */
class Checkpoint extends Model
{
    use HasUlids;
    use SoftDeletes;

    protected $guarded = [];

    /**
     * Get the parent thread model.
     *
     * @return BelongsTo<Thread, $this>
     */
    public function thread(): BelongsTo
    {
        return $this->belongsTo(Thread::class);
    }

    /**
     * Get the state as an enum instance.
     *
     * @return Attribute<(BackedEnum&HasNode)|null, (BackedEnum&HasNode)|string|null>
     */
    protected function state(): Attribute
    {
        return Attribute::make(
            get: function ($value, $attributes) {
                /** @var array<string, string> $graphConfig */
                $graphConfig = collect(config('nodegraph.graphs'))
                    ->filter(fn ($graph) => $graph['name'] === $attributes['graph_name'])
                    ->first();

                $stateEnumClass = Arr::get($graphConfig, 'state_enum');
                if ($stateEnumClass && $value) {
                    return $stateEnumClass::tryFrom($value);
                }

                return $value;
            },
            set: function ($value) {
                if ($value instanceof BackedEnum) {
                    return $value->value;
                }

                return $value;
            },
        );
    }

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }
}
