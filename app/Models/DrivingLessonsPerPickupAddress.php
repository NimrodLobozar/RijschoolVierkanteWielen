<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DrivingLessonsPerPickupAddress extends Model
{
    /** @use HasFactory<\Database\Factories\DrivingLessonsPerPickupAddressFactory> */
    use HasFactory;

    protected $table = 'driving_lessons_per_pickup_addresses';

    protected $fillable = [
        'lesson_id',
        'pickup_address_id',
        'is_active',
        'note',
    ];

    public function lesson()
    {
        return $this->belongsTo(Lessons::class, 'lesson_id');
    }

    public function pickUpAddress()
    {
        return $this->belongsTo(PickUpAddress::class, 'pickup_address_id');
    }
}
