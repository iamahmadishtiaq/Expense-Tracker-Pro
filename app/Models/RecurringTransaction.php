<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RecurringTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'account_id',
        'category_id',
        'type',
        'amount',
        'frequency',
        'start_date',
        'next_run_date',
        'description',
        'is_active'
    ];

    protected $casts = [
        'start_date' => 'date',
        'next_run_date' => 'date',
        'is_active' => 'boolean',
        'amount' => 'decimal:2',
    ];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function account(): BelongsTo {
        return $this->belongsTo(Account::class);
    }

    public function category() : BelongsTo {
        return $this->belongsTo(Category::class);
    }

    public function calculateNextRunDate(Carbon $currentDate): Carbon {
        return match ($this->frequency) {
            'daily' => $currentDate->copy()->addDay(),
            'weekly' => $currentDate->copy()->addWeek(),
            'yearly' => $currentDate->copy()->addYear(),
            default => $currentDate->copy()->addMonth(),
        };
    }
}
