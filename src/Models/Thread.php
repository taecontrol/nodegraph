<?php

namespace Taecontrol\NodeGraph\Models;

use BackedEnum;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Taecontrol\NodeGraph\Contracts\HasNode;

/**
 * @property (BackedEnum&HasNode)|null $current_state
 * @property array<string, mixed>|null $metadata
 * @property Carbon|null $started_at
 * @property Carbon|null $finished_at
 */
class Thread extends Model
{
    use HasUlids;
    use SoftDeletes;

    protected $guarded = [];

    /**
     * Get the parent threadable model (morph to).
     *
     * @return MorphTo<Model, $this>
     */
    public function threadable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the checkpoints for the thread.
     *
     * @return HasMany<Checkpoint, $this>
     */
    public function checkpoints(): HasMany
    {
        return $this->hasMany(Checkpoint::class);
    }

    /**
     * Get the current state as an enum instance.
     *
     * @return Attribute<(BackedEnum&HasNode)|null, (BackedEnum&HasNode)|string|null>
     */
    protected function currentState(): Attribute
    {
        return Attribute::make(
            get: function ($value, $attributes) {
                /** @var array<string, string> $graphConfig */
                $graphConfig = collect(config('nodegraph.graphs'))
                    ->dump()
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

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }
}
