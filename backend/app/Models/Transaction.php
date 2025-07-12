<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = [
        'post_date',
        'value_date',
        'description',
        'doctor_name',
        'reference',
        'amount',
        'balance',
        'specialist',
        'registration',
        'yearly',
        'exam',
        'certificate',
        'newsletters',
        'other',
        'visa',
        'inward_number',
        'inward_date',
        'notes',
        'is_locked',
        'completed_by_user_id',
        'completed_at'
    ];

    protected $casts = [
        'post_date' => 'date',
        'value_date' => 'date',
        'inward_date' => 'date',
        'completed_at' => 'datetime',
        'amount' => 'decimal:2',
        'balance' => 'decimal:2',
        'specialist' => 'decimal:2',
        'registration' => 'decimal:2',
        'yearly' => 'decimal:2',
        'exam' => 'decimal:2',
        'certificate' => 'decimal:2',
        'newsletters' => 'decimal:2',
        'other' => 'decimal:2',
        'visa' => 'decimal:2',
        'is_locked' => 'boolean',
    ];

    protected $appends = [
        'unspecified',
        'summary',
        'commission',
        'total',
        'difference'
    ];

    public function completedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by_user_id');
    }

    // Calculated fields
    public function getUnspecifiedAttribute(): float
    {
        return $this->specialist == 0 ? (float)$this->amount : 0;
    }

    public function getSummaryAttribute(): float
    {
        return (float)($this->registration + $this->yearly + $this->exam + 
                     $this->certificate + $this->newsletters + $this->other + $this->visa);
    }

    public function getCommissionAttribute(): float
    {
        $summary = $this->getSummaryAttribute();
        $amount = (float)$this->amount;
        return $summary >= $amount ? $summary - $amount : 0;
    }

    public function getTotalAttribute(): float
    {
        return (float)$this->amount + $this->getCommissionAttribute();
    }

    public function getDifferenceAttribute(): float
    {
        return $this->getSummaryAttribute() - $this->getTotalAttribute();
    }
}
