<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PickUpAddress extends Model
{
    /** @use HasFactory<\Database\Factories\PickUpAddressFactory> */
    use HasFactory;

    protected $table = 'pick-up_addresses';

    protected $fillable = [
        'street',
        'house_number',
        'addition',
        'postal_code',
        'city',
        'is_active',
        'note',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the lessons that use this pick-up address.
     */
    public function lessons()
    {
        return $this->belongsToMany(Lessons::class, 'driving_lessons_per_pickup_addresses', 'pickup_address_id', 'lesson_id')
            ->withTimestamps();
    }

    /**
     * Get the full address on one line.
     */
    public function getFullAddressAttribute(): string
    {
        return trim("{$this->street} {$this->house_number}{$this->addition}") . ", {$this->postal_code} {$this->city}";
    }
}
