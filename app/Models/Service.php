<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

    public function getLetterCodeAttribute(): ?string
    {
        return $this->queuePool?->letter_code;
    }

    public function queueTickets(): HasMany
    {
        return $this->hasMany(QueueTicket::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withTimestamps();
    }

    /**
     * Scope: hanya layanan aktif, diurutkan berdasarkan sort_order lalu nama.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name');
    }

    /**
     * Hitung sisa kuota harian untuk tanggal tertentu.
     * Mengembalikan null jika daily_quota tidak diset (unlimited).
     */
    public function getRemainingQuota(?string $date = null): ?int
    {
        if ($this->daily_quota === null) {
            return null;
        }

        $targetDate = $date ?? today()->toDateString();

        $usedCount = QueueTicket::forServiceOnDate($this->id, $targetDate)
            ->notCancelled()
            ->count();

        return max(0, $this->daily_quota - $usedCount);
    }

    /**
     * Periksa apakah kuota harian sudah penuh untuk tanggal tertentu.
     * Mengembalikan false jika daily_quota null (unlimited).
     */
    public function isQuotaFull(string $date): bool
    {
        if ($this->daily_quota === null) {
            return false;
        }

        return $this->getRemainingQuota($date) <= 0;
    }
}
