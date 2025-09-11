<?php

namespace Taecontrol\NodeGraph\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Thread extends Model
{
    use HasUlids;
    use SoftDeletes;

    protected $guarded = [];

    /**
     * Get the parent threadable model (morph to).
     *
     * @return MorphTo<Thread, Model>
     */
    public function threadable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the checkpoints for the thread.
     *
     * @return HasMany<Checkpoint>
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
        ];
    }
}
