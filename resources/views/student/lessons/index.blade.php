<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap gap-4 justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Mijn lessen') }}
            </h2>
            <a href="{{ route('student.lessons.create') }}"
                class="bg-green-600 text-white px-5 py-2.5 rounded-lg transition duration-300 hover:bg-green-700 transform hover:scale-105">
                {{ __('Les aanvragen') }}
            </a>
        </div>
    </x-slot>

    @php $card = 'bg-white dark:bg-gray-800 shadow-md sm:rounded-lg'; @endphp

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            <x-flash />

            {{-- Lespakketten --}}
            <section>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">Mijn lespakketten</h3>
                @if ($registrations->isEmpty())
                    <p class="{{ $card }} p-5 text-gray-500 dark:text-gray-400">
                        Je hebt nog geen lespakket. Neem contact op met de rijschool om je in te schrijven.
                    </p>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach ($registrations as $registration)
                            @php
                                $used = $registration->usedLessons();
                                $total = $registration->package->lesson_count;
                            @endphp
                            <div class="{{ $card }} p-5 {{ $registration->is_active ? '' : 'opacity-60' }}">
                                <div class="flex justify-between items-start">
                                    <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $registration->package->type }}</p>
                                    @unless ($registration->is_active)
                                        <span class="text-xs py-1 px-2 rounded-xl bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300">Inactief</span>
                                    @endunless
                                </div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $registration->start_date?->format('d-m-Y') }} - {{ $registration->end_date?->format('d-m-Y') ?? 'open' }}
                                </p>
                                <p class="mt-3 text-sm text-gray-700 dark:text-gray-300">
                                    <strong>{{ max(0, $total - $used) }}</strong> van {{ $total }} lessen nog te plannen
                                </p>
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 mt-1">
                                    <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ $total > 0 ? min(100, round($used / $total * 100)) : 0 }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            {{-- Komende lessen --}}
            <section>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">Komende lessen en aanvragen</h3>
                @if ($upcoming->isEmpty())
                    <p class="{{ $card }} p-5 text-gray-500 dark:text-gray-400">Je hebt geen komende lessen.</p>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach ($upcoming as $lesson)
                            <div class="{{ $card }} p-5 text-sm border-l-4 {{ $lesson->status === 'aangevraagd' ? 'border-yellow-400' : 'border-blue-500' }}">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $lesson->start_date->translatedFormat('l j F Y') }}</p>
                                        <p class="text-gray-600 dark:text-gray-300">{{ $lesson->time_range }} &middot; {{ $lesson->goal }}</p>
                                    </div>
                                    <x-lesson-status :status="$lesson->status" />
                                </div>
                                <p class="mt-2 text-gray-600 dark:text-gray-300">
                                    <strong>Instructeur:</strong> {{ $lesson->instructor?->user?->full_name ?? 'Nog niet bekend' }}<br>
                                    <strong>Auto:</strong> {{ $lesson->auto ? $lesson->auto->brand . ' ' . $lesson->auto->model . ' (' . $lesson->auto->license_plate . ')' : 'Nog niet bekend' }}<br>
                                    <strong>Ophalen:</strong> {{ $lesson->pickUpAddress?->full_address ?? '-' }}
                                </p>
                                @if ($lesson->canBeCancelledByStudent())
                                    <form method="POST" action="{{ route('student.lessons.cancel', $lesson) }}" class="mt-3"
                                        onsubmit="return confirm('Weet je zeker dat je deze les wilt annuleren?');">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="text-red-600 dark:text-red-400 hover:underline">
                                            {{ $lesson->status === 'aangevraagd' ? 'Aanvraag intrekken' : 'Les annuleren' }}
                                        </button>
                                    </form>
                                @else
                                    <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                                        Annuleren kan tot {{ \App\Models\Lessons::CANCEL_HOURS }} uur van tevoren. Bel de rijschool als je toch niet kunt.
                                    </p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            {{-- Geschiedenis --}}
            <section>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">Eerdere lessen</h3>
                @if ($history->isEmpty())
                    <p class="{{ $card }} p-5 text-gray-500 dark:text-gray-400">Nog geen eerdere lessen.</p>
                @else
                    <div class="space-y-4">
                        @foreach ($history as $lesson)
                            <div class="{{ $card }} p-5 text-sm">
                                <div class="flex flex-wrap justify-between items-start gap-2">
                                    <div>
                                        <p class="font-semibold text-gray-900 dark:text-gray-100">
                                            {{ $lesson->start_date->format('d-m-Y') }} &middot; {{ $lesson->time_range }} &middot; {{ $lesson->goal }}
                                        </p>
                                        <p class="text-gray-500 dark:text-gray-400">Instructeur: {{ $lesson->instructor?->user?->full_name ?? '-' }}</p>
                                    </div>
                                    <x-lesson-status :status="$lesson->status" />
                                </div>
                                @if ($lesson->instructor_comment)
                                    <p class="mt-2 p-2 rounded bg-blue-50 dark:bg-blue-900/30 text-gray-700 dark:text-gray-200">
                                        <strong>Instructeur:</strong> {{ $lesson->instructor_comment }}
                                    </p>
                                @endif
                                @if ($lesson->status === 'voltooid')
                                    <form method="POST" action="{{ route('student.lessons.comment', $lesson) }}" class="mt-3">
                                        @csrf
                                        @method('PATCH')
                                        <label for="comment_{{ $lesson->id }}" class="block text-xs font-medium text-gray-600 dark:text-gray-400">Mijn opmerking over deze les</label>
                                        <div class="flex flex-col sm:flex-row gap-2 mt-1">
                                            <textarea name="student_comment" id="comment_{{ $lesson->id }}" rows="1" maxlength="1000"
                                                class="bg-gray-200 text-gray-900 p-2 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">{{ $lesson->student_comment }}</textarea>
                                            <button type="submit" class="whitespace-nowrap px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-500">Opslaan</button>
                                        </div>
                                    </form>
                                @elseif ($lesson->student_comment)
                                    <p class="mt-2 text-gray-600 dark:text-gray-300"><strong>Mijn opmerking:</strong> {{ $lesson->student_comment }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
