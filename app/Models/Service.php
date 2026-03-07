<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    /** @use HasFactory<\Database\Factories\ServiceFactory> */
    use HasFactory;

    protected $fillable = [
        'queue_pool_id',
        'name',
        'code',
        'slug',
        'description',
        'requirements',
        'is_active',
        'booking_enabled',
        'walk_in_enabled',
        'daily_quota',
        'sort_order',
        'letter_code',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'booking_enabled' => 'boolean',
            'walk_in_enabled' => 'boolean',
            'daily_quota' => 'integer',
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
}
