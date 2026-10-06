<?php

namespace Database\Factories;

use App\Models\Auto;
use App\Models\Instructor;
use App\Models\Lessons;
use App\Models\Registration;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lessons>
 */
class LessonsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $date = $this->faker->dateTimeBetween('-2 months', '+1 month');
        $hour = $this->faker->numberBetween(8, 17);
        $isPast = $date < now();

        return [
            'registration_id' => Registration::inRandomOrder()->value('id') ?? Registration::factory(),
            'instructor_id' => Instructor::inRandomOrder()->value('id') ?? Instructor::factory(),
            'auto_id' => Auto::inRandomOrder()->value('id'),
            'start_date' => $date->format('Y-m-d'),
            'start_time' => sprintf('%02d:00', $hour),
            'end_date' => $date->format('Y-m-d'),
            'end_time' => sprintf('%02d:00', $hour + 1),
            'status' => $isPast
                ? $this->faker->randomElement([Lessons::STATUS_COMPLETED, Lessons::STATUS_COMPLETED, Lessons::STATUS_COMPLETED, Lessons::STATUS_CANCELLED])
                : Lessons::STATUS_PLANNED,
            'goal' => $this->faker->randomElement(Lessons::GOALS),
            'student_comment' => null,
            'instructor_comment' => $isPast ? $this->faker->optional(0.6)->randomElement([
                'Ging goed, volgende keer verder met invoegen.',
                'Let beter op de spiegels bij het afslaan.',
                'Mooie vooruitgang, bijna klaar voor het examen.',
                'Snelheid op rotondes nog wat rustiger.',
            ]) : null,
            'is_active' => true,
            'comment' => null,
        ];
    }

    /**
     * A lesson requested by the student, without instructor and car.
     */
    public function requested(): static
    {
        return $this->state(fn () => [
            'instructor_id' => null,
            'auto_id' => null,
            'status' => Lessons::STATUS_REQUESTED,
            'instructor_comment' => null,
        ]);
    }
}
