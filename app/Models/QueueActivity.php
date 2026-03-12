<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QueueActivity extends Model
{
    /** @use HasFactory<\Database\Factories\QueueActivityFactory> */
    use HasFactory;

    protected $fillable = [
        'queue_ticket_id',
        'user_id',
        'counter_id',
        'action',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
        ];
    }

    public function queueTicket(): BelongsTo
    {
        return $this->belongsTo(QueueTicket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function counter(): BelongsTo
    {
        return $this->belongsTo(Counter::class);
    }
}
