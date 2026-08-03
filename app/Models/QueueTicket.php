<?php

namespace App\Models;

use App\Enums\QueueStatus;
use Database\Factories\QueueTicketFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QueueTicket extends Model
{
    /** @use HasFactory<QueueTicketFactory> */
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
        'visit_purpose',
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

    public function wilayah(): BelongsTo
    {
        return $this->belongsTo(Wilayah::class, 'visitor_wilayah_kode', 'kode');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(QueueActivity::class);
    }

    /**
     * Hitung posisi antrian tiket ini di antara tiket waiting pada hari dan pool yang sama.
     */
    public function getQueuePosition(): ?int
    {
        if ($this->status !== QueueStatus::Waiting) {
            return null;
        }

        return static::query()
            ->where('queue_pool_id', $this->queue_pool_id)
            ->whereDate('service_date', $this->service_date)
            ->where('status', QueueStatus::Waiting)
            ->where('sequence_number', '<', $this->sequence_number)
            ->count() + 1;
    }

    /**
     * Scope: tiket yang belum dibatalkan.
     */
    public function scopeNotCancelled(Builder $query): Builder
    {
        return $query->whereNotIn('status', [QueueStatus::Cancelled]);
    }

    /**
     * Scope: tiket untuk layanan dan tanggal tertentu.
     */
    public function scopeForServiceOnDate(Builder $query, int $serviceId, string $date): Builder
    {
        return $query->where('service_id', $serviceId)
            ->whereDate('service_date', $date);
    }

    /**
     * Get the route key name for Laravel's route model binding.
     */
    public function getRouteKeyName(): string
    {
        return 'id';
    }
}
