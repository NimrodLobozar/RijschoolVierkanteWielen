{{-- Dashboard voor instructeurs en leerlingen: de eerstvolgende lessen --}}
@php
    $user = auth()->user();
    $isInstructor = $user->hasRole('Instructeur') && $user->instructor;
    $student = $user->student;

    $query = \App\Models\Lessons::with(['registration.student.user', 'instructor.user', 'auto', 'pickUpAddresses'])
        ->whereIn('status', [\App\Models\Lessons::STATUS_REQUESTED, \App\Models\Lessons::STATUS_PLANNED])
        ->whereDate('start_date', '>=', today())
        ->orderBy('start_date')
        ->orderBy('start_time');

    if ($isInstructor) {
        $query->where('instructor_id', $user->instructor->id);
        $openRequests = \App\Models\Lessons::where('status', \App\Models\Lessons::STATUS_REQUESTED)->whereNull('instructor_id')
            ->whereDate('start_date', '>=', today())->count();
        $link = route('instructor.lessons.index');
    } else {
        $query->whereHas('registration', fn ($q) => $q->where('student_id', $student?->id ?? 0));
        $openRequests = 0;
        $link = route('student.lessons.index');
    }

    $lessons = $query->limit(5)->get();
@endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 bg-white dark:bg-gray-800 shadow-xl sm:rounded-lg p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">Mijn eerstvolgende lessen</h3>
            <a href="{{ $link }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">Alles bekijken</a>
        </div>

        @forelse ($lessons as $lesson)
            <div class="flex justify-between items-center py-3 border-b border-gray-100 dark:border-gray-700 last:border-0 text-sm">
                <div>
                    <p class="font-semibold text-gray-900 dark:text-gray-100">
                        {{ $lesson->start_date->isToday() ? 'Vandaag' : $lesson->start_date->translatedFormat('l j F') }} &middot; {{ $lesson->time_range }}
                    </p>
                    <p class="text-gray-500 dark:text-gray-400">
                        @if ($isInstructor)
                            {{ $lesson->registration->student->user->full_name }} &middot; {{ $lesson->pickUpAddress?->full_address }}
                        @else
                            {{ $lesson->goal }} &middot; {{ $lesson->instructor?->user?->full_name ?? 'instructeur nog niet bekend' }}
                        @endif
                    </p>
                </div>
                <x-lesson-status :status="$lesson->status" />
            </div>
        @empty
            <p class="text-gray-500 dark:text-gray-400">Je hebt geen komende lessen.</p>
        @endforelse
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-xl sm:rounded-lg p-6 flex flex-col gap-3">
        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">Snelle acties</h3>
        @if ($isInstructor)
            <a href="{{ route('instructor.lessons.index') }}" class="px-4 py-3 rounded-lg bg-indigo-600 text-white hover:bg-indigo-500">Mijn lesrooster</a>
            <p class="text-sm text-gray-600 dark:text-gray-300">
                {{ $openRequests }} {{ $openRequests === 1 ? 'lesaanvraag wacht' : 'lesaanvragen wachten' }} op een instructeur.
            </p>
        @else
            <a href="{{ route('student.lessons.create') }}" class="px-4 py-3 rounded-lg bg-green-600 text-white hover:bg-green-700">Les aanvragen</a>
            <a href="{{ route('student.lessons.index') }}" class="px-4 py-3 rounded-lg bg-indigo-600 text-white hover:bg-indigo-500">Mijn lessen</a>
        @endif
    </div>
</div>
