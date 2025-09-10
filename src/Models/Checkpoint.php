<?php

namespace Taecontrol\NodeGraph\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Checkpoint extends Model
{
    use HasUlids;
    use SoftDeletes;

    public $guarded = [];

    /**
     * Get the parent thread model.
     * @return BelongsTo<Thread, self>
     */
    public function thread(): BelongsTo
    {
        return $this->belongsTo(Thread::class);
    }
}
