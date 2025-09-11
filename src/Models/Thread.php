<?php

namespace Taecontrol\NodeGraph\Models;

use BackedEnum;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
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

    protected function casts(): array
    {
        return [
            'current_state' => config('nodegraph.state_enum'),
            'metadata' => 'array',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }
}
