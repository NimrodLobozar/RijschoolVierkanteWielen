<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesLessons;
use App\Models\Auto;
use App\Models\Instructor;
use App\Models\Lessons;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Lesrooster van de ingelogde instructeur: eigen lessen beheren en lesaanvragen accepteren.
 */
class InstructorLessonController extends Controller
{
    use HandlesLessons;

    private const RELATIONS = ['registration.student.user', 'registration.student.user.contact', 'registration.package', 'auto', 'pickUpAddresses'];

    public function index()
    {
        $instructor = $this->currentInstructor();

        $upcoming = Lessons::with(self::RELATIONS)
            ->where('instructor_id', $instructor->id)
            ->where('status', Lessons::STATUS_PLANNED)
            ->whereDate('start_date', '>=', today())
            ->orderBy('start_date')
            ->orderBy('start_time')
            ->get();

        $requests = Lessons::with(self::RELATIONS)
            ->where('status', Lessons::STATUS_REQUESTED)
            ->where(fn ($q) => $q->whereNull('instructor_id')->orWhere('instructor_id', $instructor->id))
            ->whereDate('start_date', '>=', today())
            ->orderBy('start_date')
            ->orderBy('start_time')
            ->get();

        $history = Lessons::with(self::RELATIONS)
            ->where('instructor_id', $instructor->id)
            ->where(fn ($q) => $q->whereDate('start_date', '<', today())
                ->orWhereIn('status', [Lessons::STATUS_COMPLETED, Lessons::STATUS_CANCELLED]))
            ->orderByDesc('start_date')
            ->orderByDesc('start_time')
            ->paginate(15);

        $autos = Auto::where('is_active', true)->orderBy('brand')->orderBy('model')->get();

        return view('instructor.lessons.index', compact('instructor', 'upcoming', 'requests', 'history', 'autos'));
    }

    /**
     * Accepteer een lesaanvraag van een leerling en kies een auto.
     */
    public function accept(Request $request, Lessons $lesson)
    {
        $instructor = $this->currentInstructor();

        if ($lesson->status !== Lessons::STATUS_REQUESTED
            || ($lesson->instructor_id && $lesson->instructor_id !== $instructor->id)) {
            return back()->withErrors(['error' => 'Deze lesaanvraag is niet (meer) beschikbaar.']);
        }

        $data = $request->validate(
            ['auto_id' => ['required', Rule::exists('autos', 'id')->where('is_active', true)]],
            [],
            $this->lessonAttributes()
        );

        $conflict = Lessons::findConflict(
            $lesson->start_date->format('Y-m-d'),
            $lesson->start_time,
            $lesson->end_time,
            $instructor->id,
            (int) $data['auto_id'],
            $lesson->registration->student_id,
            $lesson->id
        );

        if ($conflict) {
            return back()->withErrors(['error' => $conflict]);
        }

        $lesson->update([
            'instructor_id' => $instructor->id,
            'auto_id' => $data['auto_id'],
            'status' => Lessons::STATUS_PLANNED,
        ]);

        return redirect()->route('instructor.lessons.index')->with('success', 'De lesaanvraag is geaccepteerd en ingepland.');
    }

    public function edit(Lessons $lesson)
    {
        $this->authorizeLesson($lesson);
        $lesson->load(self::RELATIONS);

        $autos = Auto::where(fn ($q) => $q->where('is_active', true)->orWhere('id', $lesson->auto_id))
            ->orderBy('brand')
            ->orderBy('model')
            ->get();

        $statuses = array_intersect_key(Lessons::STATUSES, array_flip([
            Lessons::STATUS_PLANNED,
            Lessons::STATUS_COMPLETED,
            Lessons::STATUS_CANCELLED,
        ]));
        $goals = Lessons::GOALS;

        return view('instructor.lessons.edit', compact('lesson', 'autos', 'statuses', 'goals'));
    }

    public function update(Request $request, Lessons $lesson)
    {
        $instructor = $this->authorizeLesson($lesson);

        $data = $request->validate(
            [
                'status' => ['required', Rule::in([Lessons::STATUS_PLANNED, Lessons::STATUS_COMPLETED, Lessons::STATUS_CANCELLED])],
                'auto_id' => ['required', 'exists:autos,id'],
                'goal' => ['required', 'string', 'max:255'],
                'instructor_comment' => ['nullable', 'string', 'max:1000'],
            ] + $this->lessonTimeRules() + $this->pickUpAddressRules(),
            $this->lessonMessages(),
            $this->lessonAttributes()
        );

        $registration = $lesson->registration()->with('package')->first();

        if ($data['status'] !== Lessons::STATUS_CANCELLED) {
            $this->ensureFitsRegistration($registration, $data['start_date'], $lesson->id);
            $this->ensureNoConflict($data, $instructor->id, (int) $data['auto_id'], $registration, $lesson->id);
        }

        $lesson->update($this->lessonFields($data) + [
            'status' => $data['status'],
            'auto_id' => $data['auto_id'],
            'instructor_comment' => $data['instructor_comment'] ?? null,
        ]);
        $this->savePickUpAddress($lesson, $data);

        return redirect()->route('instructor.lessons.index')->with('success', 'De les is bijgewerkt.');
    }

    private function currentInstructor(): Instructor
    {
        $instructor = auth()->user()->instructor;

        abort_if(! $instructor, 403, 'Er is geen instructeursprofiel aan dit account gekoppeld.');

        return $instructor;
    }

    private function authorizeLesson(Lessons $lesson): Instructor
    {
        $instructor = $this->currentInstructor();

        abort_if($lesson->instructor_id !== $instructor->id, 403, 'Dit is niet jouw les.');

        return $instructor;
    }
}
