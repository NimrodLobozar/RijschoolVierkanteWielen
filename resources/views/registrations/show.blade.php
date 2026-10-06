<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap gap-4 justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Inschrijving') }}: {{ $registration->student?->user?->full_name }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('registrations.edit', $registration) }}"
                    class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm hover:bg-indigo-500">Wijzigen</a>
                <a href="{{ route('lessons.create', ['registration_id' => $registration->id]) }}"
                    class="px-4 py-2 rounded-lg bg-green-600 text-white text-sm hover:bg-green-700">Les inplannen</a>
            </div>
        </div>
    </x-slot>

    @php
        $used = $registration->usedLessons();
        $total = $registration->package->lesson_count;
        $completed = $lessons->where('status', 'voltooid')->count();
    @endphp

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-flash />

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-5">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Leerling</h3>
                    <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $registration->student?->user?->full_name }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $registration->student?->relation_number }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $registration->student?->user?->contact?->email }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $registration->student?->user?->contact?->full_address }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-5">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Lespakket</h3>
                    <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $registration->package->type }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ $registration->start_date?->format('d-m-Y') }} - {{ $registration->end_date?->format('d-m-Y') ?? 'open' }}
                        &middot; {{ $registration->is_active ? 'Actief' : 'Inactief' }}
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $registration->invoices->count() }} factuur/facturen</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-5">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Lessen</h3>
                    <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $used }} van {{ $total }} ingepland of gereden</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $completed }} voltooid &middot; nog {{ max(0, $total - $used) }} over</p>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 mt-2">
                        <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ $total > 0 ? min(100, round($used / $total * 100)) : 0 }}%"></div>
                    </div>
                </div>
            </div>

            @if ($registration->note)
                <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-5 mb-6 text-sm text-gray-700 dark:text-gray-300">
                    <strong>Opmerking:</strong> {{ $registration->note }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-x-auto shadow-md sm:rounded-lg">
                @if ($lessons->isEmpty())
                    <p class="p-6 text-gray-500 dark:text-gray-400">Er zijn nog geen lessen voor deze inschrijving.</p>
                @else
                    <table class="w-full min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                @foreach (['Datum', 'Tijd', 'Instructeur', 'Auto', 'Lesdoel', 'Status', ''] as $column)
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">{{ $column }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                            @foreach ($lessons as $lesson)
                                <tr class="hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $lesson->start_date->format('d-m-Y') }}</td>
                                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $lesson->time_range }}</td>
                                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $lesson->instructor?->user?->full_name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $lesson->auto ? $lesson->auto->brand . ' ' . $lesson->auto->model : '-' }}</td>
                                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $lesson->goal }}</td>
                                    <td class="px-4 py-3"><x-lesson-status :status="$lesson->status" /></td>
                                    <td class="px-4 py-3"><a href="{{ route('lessons.edit', $lesson) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">Bewerk</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
