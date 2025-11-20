<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Correspondence extends Model
{
    use HasFactory;

    protected $fillable = [
        'apartment_id',
        'type',
        'carrier',
        'tracking_code',
        'description',
        'received_at',
        'status',
        'retrieved_at',
        'retrieved_by_name',
        'registered_by_user_id',
    ];

    protected $casts = [
        'received_at' => 'datetime',
        'retrieved_at' => 'datetime',
    ];

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    public function registeredBy()
    {
        return $this->belongsTo(User::class, 'registered_by_user_id');
    }

    public function markAsRetrieved(?string $retrievedBy = null): void
    {
        $this->fill([
            'status' => 'retirado',
            'retrieved_at' => Carbon::now(),
            'retrieved_by_name' => $retrievedBy ?? $this->retrieved_by_name,
        ])->save();
    }
}
