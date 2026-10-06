<?php

namespace App\Http\Controllers;

use App\Models\Lessons;
use App\Models\Package;
use App\Models\Registration;
use App\Models\Student;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * Inschrijvingen: een leerling koppelen aan een lespakket.
 */
class RegistrationController extends Controller
{
    public function index(Request $request)
    {
        $registrations = Registration::with(['student.user', 'package'])
            ->withCount(['lessons as used_lessons' => fn ($q) => $q->counting()])
            ->when($request->input('status') === 'actief', fn ($q) => $q->where('is_active', true))
            ->when($request->input('status') === 'inactief', fn ($q) => $q->where('is_active', false))
            ->orderByDesc('start_date')
            ->paginate(20)
            ->withQueryString();

        return view('registrations.index', compact('registrations'));
    }

    public function create(Request $request)
    {
        $registration = new Registration([
            'student_id' => $request->input('student_id'),
            'start_date' => today(),
            'is_active' => true,
        ]);

        return view('registrations.create', $this->formData($registration));
    }

    public function store(Request $request)
    {
        $data = $this->validateRegistration($request);

        try {
            Registration::create($data);
        } catch (Exception $e) {
            Log::error('Fout bij het aanmaken van de inschrijving: ' . $e->getMessage());
            return back()->withInput()->withErrors(['error' => 'Er is een fout opgetreden bij het aanmaken van de inschrijving.']);
        }

        return redirect()->route('registrations.index')->with('success', 'De inschrijving is aangemaakt.');
    }

    public function show(Registration $registration)
    {
        $registration->load(['student.user.contact', 'package', 'invoices']);

        $lessons = $registration->lessons()
            ->with(['instructor.user', 'auto', 'pickUpAddresses'])
            ->orderByDesc('start_date')
            ->orderByDesc('start_time')
            ->get();

        return view('registrations.show', compact('registration', 'lessons'));
    }

    public function edit(Registration $registration)
    {
        return view('registrations.edit', $this->formData($registration));
    }

    public function update(Request $request, Registration $registration)
    {
        $data = $this->validateRegistration($request);

        $package = Package::findOrFail($data['package_id']);
        $used = $registration->usedLessons();
        if ($package->lesson_count < $used) {
            throw ValidationException::withMessages([
                'package_id' => "Dit pakket heeft {$package->lesson_count} lessen, maar er zijn al {$used} lessen ingepland of gereden.",
            ]);
        }

        try {
            $registration->update($data);
        } catch (Exception $e) {
            Log::error('Fout bij het bijwerken van de inschrijving: ' . $e->getMessage());
            return back()->withInput()->withErrors(['error' => 'Er is een fout opgetreden bij het bijwerken van de inschrijving.']);
        }

        return redirect()->route('registrations.index')->with('success', 'De inschrijving is bijgewerkt.');
    }

    public function destroy(Registration $registration)
    {
        if ($registration->lessons()->exists() || $registration->invoices()->exists()) {
            return back()->withErrors([
                'error' => 'Deze inschrijving heeft al lessen of facturen en kan daarom niet verwijderd worden. Zet hem op inactief.',
            ]);
        }

        $registration->delete();

        return redirect()->route('registrations.index')->with('success', 'De inschrijving is verwijderd.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateRegistration(Request $request): array
    {
        $data = $request->validate(
            [
                'student_id' => ['required', 'exists:students,id'],
                'package_id' => ['required', 'exists:packages,id'],
                'start_date' => ['required', 'date'],
                'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
                'note' => ['nullable', 'string', 'max:1000'],
            ],
            [],
            [
                'student_id' => 'leerling',
                'package_id' => 'lespakket',
                'start_date' => 'startdatum',
                'end_date' => 'einddatum',
                'note' => 'opmerking',
            ]
        );

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(Registration $registration): array
    {
        $students = Student::with('user')
            ->get()
            ->sortBy(fn ($student) => $student->user->full_name);

        $packages = Package::where(fn ($q) => $q->where('is_active', true)->orWhere('id', $registration->package_id))
            ->orderBy('lesson_count')
            ->get();

        return compact('registration', 'students', 'packages');
    }
}
