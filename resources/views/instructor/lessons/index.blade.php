<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Mijn lesrooster') }}
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $instructor->user->full_name }}</p>
        </div>
    </x-slot>

    @php
        $card = 'bg-white dark:bg-gray-800 shadow-md sm:rounded-lg';
        $th = 'px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider';
    @endphp

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            <x-flash />

            {{-- Openstaande aanvragen --}}
            <section>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">
                    Lesaanvragen
                    <span class="ml-1 text-sm font-normal text-gray-500 dark:text-gray-400">({{ $requests->count() }})</span>
                </h3>
                @if ($requests->isEmpty())
                    <p class="{{ $card }} p-5 text-gray-500 dark:text-gray-400">Er zijn geen openstaande lesaanvragen.</p>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach ($requests as $lesson)
                            <div class="{{ $card }} p-5 text-sm border-l-4 border-yellow-400">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $lesson->registration->student->user->full_name }}</p>
                                        <p class="text-gray-600 dark:text-gray-300">{{ $lesson->start_date->translatedFormat('l j F Y') }} &middot; {{ $lesson->time_range }}</p>
                                    </div>
                                    <x-lesson-status :status="$lesson->status" />
                                </div>
                                <p class="mt-2 text-gray-600 dark:text-gray-300"><strong>Lesdoel:</strong> {{ $lesson->goal }}</p>
                                <p class="text-gray-600 dark:text-gray-300"><strong>Ophalen:</strong> {{ $lesson->pickUpAddress?->full_address ?? '-' }}</p>
                                @if ($lesson->student_comment)
                                    <p class="text-gray-600 dark:text-gray-300"><strong>Opmerking:</strong> {{ $lesson->student_comment }}</p>
                                @endif
                                <form method="POST" action="{{ route('instructor.lessons.accept', $lesson) }}" class="mt-4 flex flex-col sm:flex-row gap-2">
                                    @csrf
                                    <label for="auto_{{ $lesson->id }}" class="sr-only">Auto</label>
                                    <select name="auto_id" id="auto_{{ $lesson->id }}" required
                                        class="bg-gray-200 text-gray-900 p-2 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                                        <option value="">Kies een auto...</option>
                                        @foreach ($autos as $auto)
                                            <option value="{{ $auto->id }}">{{ $auto->brand }} {{ $auto->model }} ({{ $auto->license_plate }})</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="whitespace-nowrap px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">Accepteren</button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            {{-- Komende lessen --}}
            <section>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">
                    Komende lessen
                    <span class="ml-1 text-sm font-normal text-gray-500 dark:text-gray-400">({{ $upcoming->count() }})</span>
                </h3>
                @if ($upcoming->isEmpty())
                    <p class="{{ $card }} p-5 text-gray-500 dark:text-gray-400">Je hebt geen komende lessen.</p>
                @else
                    <div class="{{ $card }} overflow-x-auto">
                        <table class="w-full min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    @foreach (['Datum', 'Tijd', 'Leerling', 'Ophaaladres', 'Auto', 'Lesdoel', ''] as $column)
                                        <th scope="col" class="{{ $th }}">{{ $column }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                                @foreach ($upcoming as $lesson)
                                    <tr class="hover:bg-gray-100 dark:hover:bg-gray-700 {{ $lesson->start_date->isToday() ? 'bg-blue-50 dark:bg-blue-900/20' : '' }}">
                                        <td class="px-4 py-3 whitespace-nowrap text-gray-900 dark:text-gray-100">
                                            {{ $lesson->start_date->isToday() ? 'Vandaag' : $lesson->start_date->translatedFormat('D d-m-Y') }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-gray-500 dark:text-gray-400">{{ $lesson->time_range }}</td>
                                        <td class="px-4 py-3 text-blue-700 dark:text-blue-500 font-medium">
                                            {{ $lesson->registration->student->user->full_name }}
                                            <div class="text-xs text-gray-500 font-normal">{{ $lesson->registration->student->user->contact?->mobile }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $lesson->pickUpAddress?->full_address ?? '-' }}</td>
                                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $lesson->auto ? $lesson->auto->brand . ' ' . $lesson->auto->model : '-' }}</td>
                                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $lesson->goal }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <a href="{{ route('instructor.lessons.edit', $lesson) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">Beheren</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>

            {{-- Geschiedenis --}}
            <section>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">Eerdere en afgeronde lessen</h3>
                @if ($history->isEmpty())
                    <p class="{{ $card }} p-5 text-gray-500 dark:text-gray-400">Nog geen eerdere lessen.</p>
                @else
                    <div class="{{ $card }} overflow-x-auto">
                        <table class="w-full min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    @foreach (['Datum', 'Tijd', 'Leerling', 'Lesdoel', 'Status', 'Opmerking', ''] as $column)
                                        <th scope="col" class="{{ $th }}">{{ $column }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                                @foreach ($history as $lesson)
                                    <tr class="hover:bg-gray-100 dark:hover:bg-gray-700">
                                        <td class="px-4 py-3 whitespace-nowrap text-gray-900 dark:text-gray-100">{{ $lesson->start_date->format('d-m-Y') }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-gray-500 dark:text-gray-400">{{ $lesson->time_range }}</td>
                                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $lesson->registration->student->user->full_name }}</td>
                                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $lesson->goal }}</td>
                                        <td class="px-4 py-3"><x-lesson-status :status="$lesson->status" /></td>
                                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400 max-w-xs truncate" title="{{ $lesson->instructor_comment }}">{{ $lesson->instructor_comment ?? '-' }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <a href="{{ route('instructor.lessons.edit', $lesson) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">Beheren</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $history->links() }}</div>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
