<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Apartment extends Model
{
    use HasFactory;

    protected $fillable = [
        'block_id',
        'side',
        'number',
        'status',
        'notes',
    ];

    protected $casts = [
        'block_id' => 'integer',
    ];

    protected $appends = [
        'display_name',
    ];

    public function block()
    {
        return $this->belongsTo(Block::class);
    }

    public function residents()
    {
        return $this->belongsToMany(User::class)
            ->using(ApartmentResident::class)
            ->withPivot(['responsibility_type', 'is_primary'])
            ->withTimestamps();
    }

    public function waterCharges()
    {
        return $this->hasMany(WaterCharge::class);
    }

    public function correspondences()
    {
        return $this->hasMany(Correspondence::class);
    }

    public function scopeOccupied($query)
    {
        return $query->where('status', 'ocupado');
    }

    protected function displayName(): Attribute
    {
        return Attribute::get(function () {
            $block = $this->block?->name ?? 'Bloco';

            return sprintf('%s %s%s', $block, strtoupper($this->side), $this->number);
        });
    }
}
