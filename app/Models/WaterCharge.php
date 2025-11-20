<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class WaterCharge extends Model
{
    use HasFactory;

    public const DEFAULT_AMOUNT = 30;

    protected $fillable = [
        'apartment_id',
        'competence',
        'amount',
        'due_date',
        'status',
        'paid_at',
        'recorded_by_user_id',
        'payment_proof_path',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'date',
        'paid_at' => 'datetime',
    ];

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }

    public function scopeForCompetence($query, string $competence)
    {
        return $query->where('competence', $competence);
    }

    public function markAsPaid(?int $userId = null): void
    {
        $this->fill([
            'status' => 'pago',
            'paid_at' => Carbon::now(),
            'recorded_by_user_id' => $userId ?? $this->recorded_by_user_id,
        ])->save();
    }

    public function markAsOpen(): void
    {
        $this->fill([
            'status' => 'aberto',
            'paid_at' => null,
        ])->save();
    }

    public function getIsOverdueAttribute(): bool
    {
        if ($this->status === 'pago') {
            return false;
        }

        return $this->due_date->isPast();
    }
}
