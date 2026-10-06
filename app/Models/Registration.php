<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Registration extends Model
{
    /** @use HasFactory<\Database\Factories\RegistrationFactory> */
    use HasFactory;

    protected $table = 'registrations';

    protected $fillable = [
        'student_id',
        'package_id',
        'start_date',
        'end_date',
        'is_active',
        'note',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Get the student of the registration.
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the package of the registration.
     */
    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    /**
     * Get the lessons of the registration.
     */
    public function lessons()
    {
        return $this->hasMany(Lessons::class);
    }

    /**
     * Get the invoices of the registration.
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Number of lessons that count towards the package (not cancelled).
     */
    public function usedLessons(?int $ignoreLessonId = null): int
    {
        return $this->lessons()
            ->counting()
            ->when($ignoreLessonId, fn ($q) => $q->where('id', '!=', $ignoreLessonId))
            ->count();
    }

    /**
     * Number of lessons the student can still plan within the package.
     */
    public function remainingLessons(?int $ignoreLessonId = null): int
    {
        return max(0, (int) $this->package->lesson_count - $this->usedLessons($ignoreLessonId));
    }

    /**
     * Whether the given date falls inside the registration period.
     */
    public function coversDate(string $date): bool
    {
        if ($this->start_date && $date < $this->start_date->format('Y-m-d')) {
            return false;
        }

        return ! ($this->end_date && $date > $this->end_date->format('Y-m-d'));
    }

    /**
     * Label for select lists, e.g. "Jan Jansen - 20 lessen (vanaf 01-09-2026)".
     */
    public function getLabelAttribute(): string
    {
        $name = $this->student?->user?->full_name ?? 'Onbekende leerling';
        $package = $this->package?->type ?? 'Onbekend pakket';

        return "{$name} - {$package} (vanaf {$this->start_date?->format('d-m-Y')})";
    }
}
