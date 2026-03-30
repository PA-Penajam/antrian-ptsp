<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Counter extends Model
{
    /** @use HasFactory<\Database\Factories\CounterFactory> */
    use HasFactory;

    protected $fillable = [
        'queue_pool_id',
        'name',
        'code',
        'is_active',
        'is_fixed',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_fixed' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function queuePool(): BelongsTo
    {
        return $this->belongsTo(QueuePool::class);
    }

    public function queueTickets(): HasMany
    {
        return $this->hasMany(QueueTicket::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(CounterSession::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(QueueActivity::class);
    }
}
