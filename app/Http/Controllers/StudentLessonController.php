<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesLessons;
use App\Models\Lessons;
use App\Models\Registration;
use App\Models\Student;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

/**
 * Lessen van de ingelogde leerling: overzicht, les aanvragen, annuleren en opmerkingen.
 */
class StudentLessonController extends Controller
{
    use HandlesLessons;

    public function index()
    {
        $student = $this->currentStudent();

        $registrations = $student->registrations()
            ->with('package')
            ->orderByDesc('is_active')
            ->orderByDesc('start_date')
            ->get();

        $lessons = Lessons::with(['instructor.user', 'auto', 'pickUpAddresses', 'registration.package'])
            ->whereIn('registration_id', $registrations->pluck('id'))
            ->get();

        $upcoming = $lessons
            ->filter(fn ($lesson) => $lesson->start_date->gte(today())
                && in_array($lesson->status, [Lessons::STATUS_REQUESTED, Lessons::STATUS_PLANNED]))
            ->sortBy(fn ($lesson) => $lesson->starts_at)
            ->values();

        $history = $lessons
            ->diff($upcoming)
            ->sortByDesc(fn ($lesson) => $lesson->starts_at)
            ->values();

        return view('student.lessons.index', compact('student', 'registrations', 'upcoming', 'history'));
    }

    public function create()
    {
        $student = $this->currentStudent();
        $registrations = $this->bookableRegistrations($student);
        $contact = auth()->user()->contact;
        $goals = Lessons::GOALS;

        return view('student.lessons.create', compact('registrations', 'contact', 'goals'));
    }

    public function store(Request $request)
    {
        $student = $this->currentStudent();

        $rules = $this->lessonTimeRules();
        $rules['start_date'][] = 'after:today';

        $data = $request->validate(
            [
                'registration_id' => [
                    'required',
                    Rule::exists('registrations', 'id')->where('student_id', $student->id)->where('is_active', true),
                ],
                'goal' => ['required', 'string', 'max:255'],
                'student_comment' => ['nullable', 'string', 'max:1000'],
            ] + $rules + $this->pickUpAddressRules(),
            $this->lessonMessages(),
            $this->lessonAttributes()
        );

        $registration = Registration::with('package')->findOrFail($data['registration_id']);
        $this->ensureFitsRegistration($registration, $data['start_date']);
        $this->ensureNoConflict($data, null, null, $registration);

        try {
            DB::transaction(function () use ($data) {
                $lesson = Lessons::create($this->lessonFields($data) + [
                    'registration_id' => $data['registration_id'],
                    'status' => Lessons::STATUS_REQUESTED,
                    'student_comment' => $data['student_comment'] ?? null,
                    'is_active' => true,
                ]);
                $this->savePickUpAddress($lesson, $data);
            });
        } catch (Exception $e) {
            Log::error('Fout bij het aanvragen van de les: ' . $e->getMessage());
            return back()->withInput()->withErrors(['error' => 'Er is een fout opgetreden bij het aanvragen van de les.']);
        }

        return redirect()->route('student.lessons.index')
            ->with('success', 'Je lesaanvraag is verstuurd. Je ziet hem als "Gepland" zodra een instructeur hem heeft geaccepteerd.');
    }

    public function cancel(Lessons $lesson)
    {
        $this->authorizeLesson($lesson);

        if (! $lesson->canBeCancelledByStudent()) {
            return back()->withErrors([
                'error' => 'Deze les kan niet meer geannuleerd worden. Lessen annuleren kan tot '
                    . Lessons::CANCEL_HOURS . ' uur van tevoren; neem anders contact op met de rijschool.',
            ]);
        }

        $lesson->update(['status' => Lessons::STATUS_CANCELLED]);

        return redirect()->route('student.lessons.index')->with('success', 'De les is geannuleerd.');
    }

    public function comment(Request $request, Lessons $lesson)
    {
        $this->authorizeLesson($lesson);

        $data = $request->validate(
            ['student_comment' => ['nullable', 'string', 'max:1000']],
            [],
            $this->lessonAttributes()
        );

        $lesson->update(['student_comment' => $data['student_comment'] ?? null]);

        return redirect()->route('student.lessons.index')->with('success', 'Je opmerking is opgeslagen.');
    }

    private function currentStudent(): Student
    {
        $student = auth()->user()->student;

        abort_if(! $student, 403, 'Er is geen leerlingprofiel aan dit account gekoppeld.');

        return $student;
    }

    private function authorizeLesson(Lessons $lesson): void
    {
        $student = $this->currentStudent();

        abort_if($lesson->registration?->student_id !== $student->id, 403, 'Dit is niet jouw les.');
    }

    /**
     * Actieve inschrijvingen die nog lessen over hebben en niet verlopen zijn.
     */
    private function bookableRegistrations(Student $student)
    {
        return $student->registrations()
            ->with('package')
            ->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('end_date')->orWhereDate('end_date', '>', today()))
            ->get()
            ->filter(fn ($registration) => $registration->remainingLessons() > 0)
            ->values();
    }
}
