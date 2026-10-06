<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesLessons;
use App\Models\Auto;
use App\Models\Instructor;
use App\Models\Lessons;
use App\Models\Registration;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

/**
 * Lesbeheer voor de administratie: alle lessen inplannen, wijzigen en verwijderen.
 */
class LessonsController extends Controller
{
    use HandlesLessons;

    public function index(Request $request)
    {
        $period = $request->input('periode', 'komend');

        $lessons = Lessons::with(['registration.student.user', 'registration.package', 'instructor.user', 'auto', 'pickUpAddresses'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('instructor_id'), fn ($q) => $q->where('instructor_id', $request->input('instructor_id')))
            ->when($period === 'komend', fn ($q) => $q->whereDate('start_date', '>=', today()))
            ->when($period === 'verleden', fn ($q) => $q->whereDate('start_date', '<', today()))
            ->when(
                $period === 'komend',
                fn ($q) => $q->orderBy('start_date')->orderBy('start_time'),
                fn ($q) => $q->orderByDesc('start_date')->orderByDesc('start_time')
            )
            ->paginate(20)
            ->withQueryString();

        $openRequests = Lessons::where('status', Lessons::STATUS_REQUESTED)->count();
        $instructors = $this->instructors();
        $statuses = Lessons::STATUSES;

        return view('lessons.index', compact('lessons', 'openRequests', 'instructors', 'statuses', 'period'));
    }

    public function create(Request $request)
    {
        $lesson = new Lessons([
            'registration_id' => $request->input('registration_id'),
            'status' => Lessons::STATUS_PLANNED,
            'start_date' => today()->addDay(),
            'start_time' => '09:00',
            'end_time' => '10:00',
        ]);

        return view('lessons.create', $this->formData($lesson));
    }

    public function store(Request $request)
    {
        $data = $this->validateLesson($request);
        $registration = Registration::with('package')->findOrFail($data['registration_id']);

        if ($data['status'] !== Lessons::STATUS_CANCELLED) {
            $this->ensureFitsRegistration($registration, $data['start_date']);
            $this->ensureNoConflict($data, $data['instructor_id'] ?? null, $data['auto_id'] ?? null, $registration);
        }

        try {
            DB::transaction(function () use ($data) {
                $lesson = Lessons::create($this->adminFields($data) + ['is_active' => true]);
                $this->savePickUpAddress($lesson, $data);
            });
        } catch (Exception $e) {
            Log::error('Fout bij het aanmaken van de les: ' . $e->getMessage());
            return back()->withInput()->withErrors(['error' => 'Er is een fout opgetreden bij het aanmaken van de les.']);
        }

        return redirect()->route('lessons.index')->with('success', 'De les is ingepland.');
    }

    public function edit(Lessons $lesson)
    {
        $lesson->load('pickUpAddresses', 'registration.student.user');

        return view('lessons.edit', $this->formData($lesson));
    }

    public function update(Request $request, Lessons $lesson)
    {
        $data = $this->validateLesson($request);
        $registration = Registration::with('package')->findOrFail($data['registration_id']);

        if ($data['status'] !== Lessons::STATUS_CANCELLED) {
            $this->ensureFitsRegistration($registration, $data['start_date'], $lesson->id);
            $this->ensureNoConflict($data, $data['instructor_id'] ?? null, $data['auto_id'] ?? null, $registration, $lesson->id);
        }

        try {
            DB::transaction(function () use ($data, $lesson) {
                $lesson->update($this->adminFields($data));
                $this->savePickUpAddress($lesson, $data);
            });
        } catch (Exception $e) {
            Log::error('Fout bij het bijwerken van de les: ' . $e->getMessage());
            return back()->withInput()->withErrors(['error' => 'Er is een fout opgetreden bij het bijwerken van de les.']);
        }

        return redirect()->route('lessons.index')->with('success', 'De les is bijgewerkt.');
    }

    public function destroy(Lessons $lesson)
    {
        try {
            DB::transaction(function () use ($lesson) {
                $lesson->pickUpAddresses()->detach();
                $lesson->delete();
            });
        } catch (Exception $e) {
            Log::error('Fout bij het verwijderen van de les: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Er is een fout opgetreden bij het verwijderen van de les.']);
        }

        return redirect()->route('lessons.index')->with('success', 'De les is verwijderd.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateLesson(Request $request): array
    {
        $needsPlanning = 'required_unless:status,' . Lessons::STATUS_REQUESTED . ',' . Lessons::STATUS_CANCELLED;

        return $request->validate(
            [
                'registration_id' => ['required', 'exists:registrations,id'],
                'status' => ['required', Rule::in(array_keys(Lessons::STATUSES))],
                'instructor_id' => ['nullable', $needsPlanning, 'exists:instructors,id'],
                'auto_id' => ['nullable', $needsPlanning, 'exists:autos,id'],
                'goal' => ['required', 'string', 'max:255'],
                'comment' => ['nullable', 'string', 'max:1000'],
                'instructor_comment' => ['nullable', 'string', 'max:1000'],
            ] + $this->lessonTimeRules() + $this->pickUpAddressRules(),
            $this->lessonMessages(),
            $this->lessonAttributes()
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function adminFields(array $data): array
    {
        return $this->lessonFields($data) + [
            'registration_id' => $data['registration_id'],
            'instructor_id' => $data['instructor_id'] ?? null,
            'auto_id' => $data['auto_id'] ?? null,
            'status' => $data['status'],
            'comment' => $data['comment'] ?? null,
            'instructor_comment' => $data['instructor_comment'] ?? null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(Lessons $lesson): array
    {
        $registrations = Registration::with(['student.user.contact', 'package'])
            ->where(fn ($q) => $q->where('is_active', true)->orWhere('id', $lesson->registration_id))
            ->orderByDesc('start_date')
            ->get()
            ->sortBy(fn ($registration) => $registration->label);

        $autos = Auto::where(fn ($q) => $q->where('is_active', true)->orWhere('id', $lesson->auto_id))
            ->orderBy('brand')
            ->orderBy('model')
            ->get();

        return [
            'lesson' => $lesson,
            'registrations' => $registrations,
            'instructors' => $this->instructors(),
            'autos' => $autos,
            'statuses' => Lessons::STATUSES,
            'goals' => Lessons::GOALS,
        ];
    }

    private function instructors()
    {
        return Instructor::with('user')->get()->sortBy(fn ($instructor) => $instructor->user->full_name);
    }
}
