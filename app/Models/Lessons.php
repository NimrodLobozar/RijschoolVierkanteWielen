<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lessons extends Model
{
    /** @use HasFactory<\Database\Factories\LessonsFactory> */
    use HasFactory;

    public const STATUS_REQUESTED = 'aangevraagd';
    public const STATUS_PLANNED = 'gepland';
    public const STATUS_COMPLETED = 'voltooid';
    public const STATUS_CANCELLED = 'geannuleerd';

    /**
     * Statussen met hun Nederlandse label.
     *
     * @var array<string, string>
     */
    public const STATUSES = [
        self::STATUS_REQUESTED => 'Aangevraagd',
        self::STATUS_PLANNED => 'Gepland',
        self::STATUS_COMPLETED => 'Voltooid',
        self::STATUS_CANCELLED => 'Geannuleerd',
    ];

    /**
     * Voorgestelde lesdoelen voor de formulieren.
     *
     * @var array<int, string>
     */
    public const GOALS = [
        'Kennismaking en eerste rijles',
        'Sturen, schakelen en remmen',
        'Kruispunten en voorrang',
        'Rotondes',
        'Invoegen en snelweg',
        'Bijzondere verrichtingen',
        'Rijden in de stad',
        'Examentraining',
        'Proefexamen',
    ];

    /**
     * Hoeveel uur van tevoren een leerling een les mag annuleren.
     */
    public const CANCEL_HOURS = 24;

    protected $table = 'lessons';

    protected $fillable = [
        'registration_id',
        'instructor_id',
        'auto_id',
        'start_date',
        'start_time',
        'end_date',
        'end_time',
        'status',
        'goal',
        'student_comment',
        'instructor_comment',
        'is_active',
        'comment',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Get the registration (student + package) of the lesson.
     */
    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }

    /**
     * Get the instructor of the lesson.
     */
    public function instructor()
    {
        return $this->belongsTo(Instructor::class);
    }

    /**
     * Get the car used for the lesson.
     */
    public function auto()
    {
        return $this->belongsTo(Auto::class);
    }

    /**
     * Get the pick-up addresses of the lesson.
     */
    public function pickUpAddresses()
    {
        return $this->belongsToMany(PickUpAddress::class, 'driving_lessons_per_pickup_addresses', 'lesson_id', 'pickup_address_id')
            ->withTimestamps();
    }

    /**
     * Get the (first) pick-up address of the lesson.
     */
    public function getPickUpAddressAttribute(): ?PickUpAddress
    {
        return $this->pickUpAddresses->first();
    }

    /**
     * Start of the lesson as a date-time.
     */
    public function getStartsAtAttribute(): Carbon
    {
        return Carbon::parse($this->start_date->format('Y-m-d') . ' ' . $this->start_time);
    }

    /**
     * Get the Dutch label of the status.
     */
    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst((string) $this->status);
    }

    /**
     * Get the time range, e.g. "09:00 - 10:00".
     */
    public function getTimeRangeAttribute(): string
    {
        return substr($this->start_time, 0, 5) . ' - ' . substr($this->end_time, 0, 5);
    }

    /**
     * Whether a student may still cancel this lesson.
     */
    public function canBeCancelledByStudent(): bool
    {
        if ($this->status === self::STATUS_REQUESTED) {
            return true;
        }

        return $this->status === self::STATUS_PLANNED
            && $this->starts_at->greaterThan(now()->addHours(self::CANCEL_HOURS));
    }

    /**
     * Lessons that count towards the package (everything except cancelled ones).
     */
    public function scopeCounting(Builder $query): Builder
    {
        return $query->where('status', '!=', self::STATUS_CANCELLED);
    }

    /**
     * Lessons that overlap with the given date and time range.
     */
    public function scopeOverlapping(Builder $query, string $date, string $startTime, string $endTime): Builder
    {
        return $query->whereDate('start_date', $date)
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime);
    }

    /**
     * Check if a planned lesson clashes with another lesson of the same
     * instructor, car or student. Returns a Dutch error message or null.
     */
    public static function findConflict(
        string $date,
        string $startTime,
        string $endTime,
        ?int $instructorId,
        ?int $autoId,
        ?int $studentId,
        ?int $ignoreLessonId = null
    ): ?string {
        $base = fn () => self::query()
            ->counting()
            ->overlapping($date, $startTime, $endTime)
            ->when($ignoreLessonId, fn ($q) => $q->where('id', '!=', $ignoreLessonId));

        if ($instructorId && $base()->where('instructor_id', $instructorId)->exists()) {
            return 'De instructeur heeft op dit moment al een andere les.';
        }

        if ($autoId && $base()->where('auto_id', $autoId)->exists()) {
            return 'Deze auto is op dit moment al ingepland voor een andere les.';
        }

        if ($studentId && $base()->whereHas('registration', fn ($q) => $q->where('student_id', $studentId))->exists()) {
            return 'De leerling heeft op dit moment al een andere les.';
        }

        return null;
    }
}
