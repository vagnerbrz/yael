<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ApartmentResident extends Pivot
{
    protected $table = 'apartment_user';

    protected $fillable = [
        'apartment_id',
        'user_id',
        'responsibility_type',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
