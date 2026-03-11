<?php

namespace App\Models;

use App\Enums\QueueStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QueueTicket extends Model
{
    /** @use HasFactory<\Database\Factories\QueueTicketFactory> */
    use HasFactory;

    protected $fillable = [
        'service_id',
        'queue_pool_id',
        'counter_id',
        'created_by',
        'channel',
        'ticket_number',
        'sequence_number',
        'service_date',
        'visitor_name',
        'visitor_identifier',
        'visitor_phone',
        'visitor_wilayah_kode',
        'notes',
        'status',
        'checked_in_at',
        'called_at',
        'started_at',
        'completed_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'sequence_number' => 'integer',
            'service_date' => 'date',
            'checked_in_at' => 'datetime',
            'called_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'status' => QueueStatus::class,
        ];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function queuePool(): BelongsTo
    {
        return $this->belongsTo(QueuePool::class);
    }

    public function counter(): BelongsTo
    {
        return $this->belongsTo(Counter::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(QueueActivity::class);
    }

    /**
     * Get the route key name for Laravel's route model binding.
     */
    public function getRouteKeyName(): string
    {
        return 'id';
    }
}
