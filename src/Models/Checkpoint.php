<?php

namespace Taecontrol\NodeGraph\Models;

use BackedEnum;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
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

    protected function casts(): array
    {
        return [
            'state' => config('nodegraph.state_enum'),
            'metadata' => 'array',
        ];
    }
}
