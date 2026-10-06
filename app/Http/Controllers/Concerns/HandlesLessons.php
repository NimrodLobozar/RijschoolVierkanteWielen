<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Lessons;
use App\Models\PickUpAddress;
use App\Models\Registration;
use Illuminate\Validation\ValidationException;

trait HandlesLessons
{
    /**
     * Nederlandse veldnamen voor de validatiemeldingen.
     *
     * @return array<string, string>
     */
    protected function lessonAttributes(): array
    {
        return [
            'registration_id' => 'inschrijving',
            'instructor_id' => 'instructeur',
            'auto_id' => 'auto',
            'start_date' => 'datum',
            'start_time' => 'begintijd',
            'end_time' => 'eindtijd',
            'status' => 'status',
            'goal' => 'lesdoel',
            'street' => 'straat',
            'house_number' => 'huisnummer',
            'addition' => 'toevoeging',
            'postal_code' => 'postcode',
            'city' => 'plaats',
            'comment' => 'opmerking',
            'student_comment' => 'opmerking van de leerling',
            'instructor_comment' => 'opmerking van de instructeur',
        ];
    }

    /**
     * Extra Nederlandse meldingen voor regels die niet goed vertaald zijn.
     *
     * @return array<string, string>
     */
    protected function lessonMessages(): array
    {
        return [
            'required_unless' => 'Kies een :attribute om de les in te plannen.',
            'end_time.after' => 'De eindtijd moet na de begintijd liggen.',
            'start_date.after' => 'Je kunt een les ten vroegste voor morgen aanvragen.',
        ];
    }

    /**
     * Validatieregels voor het ophaaladres.
     *
     * @return array<string, array<int, string>>
     */
    protected function pickUpAddressRules(): array
    {
        return [
            'street' => ['required', 'string', 'max:255'],
            'house_number' => ['required', 'string', 'max:10'],
            'addition' => ['nullable', 'string', 'max:10'],
            'postal_code' => ['required', 'string', 'max:10'],
            'city' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * Validatieregels voor datum en tijd van een les.
     *
     * @return array<string, array<int, string>>
     */
    protected function lessonTimeRules(): array
    {
        return [
            'start_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        ];
    }

    /**
     * Controleer of de les binnen de inschrijving past: periode en aantal lessen.
     *
     * @throws ValidationException
     */
    protected function ensureFitsRegistration(Registration $registration, string $date, ?int $ignoreLessonId = null): void
    {
        if (! $registration->coversDate($date)) {
            $period = 'vanaf ' . $registration->start_date->format('d-m-Y')
                . ($registration->end_date ? ' tot en met ' . $registration->end_date->format('d-m-Y') : '');

            throw ValidationException::withMessages([
                'start_date' => "De datum valt buiten de looptijd van het lespakket ({$period}).",
            ]);
        }

        if ($registration->remainingLessons($ignoreLessonId) <= 0) {
            throw ValidationException::withMessages([
                'registration_id' => 'Alle lessen van dit lespakket zijn al gebruikt ('
                    . $registration->package->lesson_count . ' van ' . $registration->package->lesson_count . ').',
            ]);
        }
    }

    /**
     * Controleer op dubbele boekingen van instructeur, auto of leerling.
     *
     * @throws ValidationException
     */
    protected function ensureNoConflict(array $data, ?int $instructorId, ?int $autoId, Registration $registration, ?int $ignoreLessonId = null): void
    {
        $conflict = Lessons::findConflict(
            $data['start_date'],
            $data['start_time'],
            $data['end_time'],
            $instructorId,
            $autoId,
            $registration->student_id,
            $ignoreLessonId
        );

        if ($conflict) {
            throw ValidationException::withMessages(['start_time' => $conflict]);
        }
    }

    /**
     * Sla het ophaaladres op en koppel het aan de les.
     */
    protected function savePickUpAddress(Lessons $lesson, array $data): void
    {
        $address = PickUpAddress::firstOrCreate(
            [
                'street' => trim($data['street']),
                'house_number' => trim($data['house_number']),
                'addition' => isset($data['addition']) && trim($data['addition']) !== '' ? trim($data['addition']) : null,
                'postal_code' => strtoupper(str_replace(' ', '', $data['postal_code'])),
                'city' => trim($data['city']),
            ],
            ['is_active' => true]
        );

        $lesson->pickUpAddresses()->sync([$address->id]);
    }

    /**
     * Haal de lesvelden uit de gevalideerde data.
     *
     * @return array<string, mixed>
     */
    protected function lessonFields(array $data): array
    {
        return [
            'start_date' => $data['start_date'],
            'end_date' => $data['start_date'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'goal' => $data['goal'],
        ];
    }
}
