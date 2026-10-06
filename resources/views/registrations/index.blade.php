<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap gap-4 justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ __('Inschrijvingen') }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Welke leerling welk lespakket heeft.</p>
            </div>
            <a href="{{ route('registrations.create') }}"
                class="bg-green-600 text-white px-5 py-2.5 rounded-lg transition duration-300 hover:bg-green-700 transform hover:scale-105">
                {{ __('Inschrijving toevoegen') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-flash />

            <div class="mb-4 flex gap-2 text-sm">
                @foreach (['' => 'Alle', 'actief' => 'Actief', 'inactief' => 'Inactief'] as $value => $text)
                    <a href="{{ route('registrations.index', array_filter(['status' => $value])) }}"
                        class="px-3 py-1 rounded-full {{ request('status', '') === $value ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 shadow' }}">
                        {{ $text }}
                    </a>
                @endforeach
            </div>

            @if ($registrations->count() > 0)
                <div class="bg-white dark:bg-gray-800 overflow-x-auto shadow-md sm:rounded-lg">
                    <table class="w-full min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                @foreach (['Leerling', 'Lespakket', 'Periode', 'Lessen', 'Status', 'Acties'] as $column)
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">{{ $column }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                            @foreach ($registrations as $registration)
                                @php
                                    $total = $registration->package->lesson_count ?? 0;
                                    $percentage = $total > 0 ? min(100, round($registration->used_lessons / $total * 100)) : 0;
                                @endphp
                                <tr class="hover:bg-gray-100 dark:hover:bg-gray-700 text-sm">
                                    <td class="px-4 py-3 font-medium text-blue-700 dark:text-blue-500">
                                        <a href="{{ route('registrations.show', $registration) }}" class="hover:underline">
                                            {{ $registration->student?->user?->full_name ?? 'Onbekend' }}
                                        </a>
                                        <div class="text-xs text-gray-500 font-normal">{{ $registration->student?->relation_number }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $registration->package?->type }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-gray-500 dark:text-gray-400">
                                        {{ $registration->start_date?->format('d-m-Y') }} - {{ $registration->end_date?->format('d-m-Y') ?? 'open' }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400 min-w-32">
                                        {{ $registration->used_lessons }} / {{ $total }}
                                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5 mt-1">
                                            <div class="bg-indigo-600 h-1.5 rounded-full" style="width: {{ $percentage }}%"></div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="py-1 px-2 rounded-xl text-xs {{ $registration->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900/60 dark:text-green-200' : 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300' }}">
                                            {{ $registration->is_active ? 'Actief' : 'Inactief' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="flex items-center space-x-3">
                                            <a href="{{ route('registrations.show', $registration) }}" class="text-blue-600 hover:text-blue-500" title="Bekijk">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-7.5 9.75-7.5 9.75 7.5 9.75 7.5-3.75 7.5-9.75 7.5S2.25 12 2.25 12z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0z" />
                                                </svg>
                                            </a>
                                            <a href="{{ route('registrations.edit', $registration) }}" class="text-indigo-600 hover:text-indigo-500" title="Bewerk">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                                                    <path d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-8.4 8.4a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32l8.4-8.4Z" />
                                                    <path d="M5.25 5.25a3 3 0 0 0-3 3v10.5a3 3 0 0 0 3 3h10.5a3 3 0 0 0 3-3V13.5a.75.75 0 0 0-1.5 0v5.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5V8.25a1.5 1.5 0 0 1 1.5-1.5h5.25a.75.75 0 0 0 0-1.5H5.25Z" />
                                                </svg>
                                            </a>
                                            <a href="{{ route('lessons.create', ['registration_id' => $registration->id]) }}"
                                                class="text-xs px-2 py-1 rounded bg-green-600 text-white hover:bg-green-700" title="Les inplannen">+ Les</a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-6">{{ $registrations->links() }}</div>
            @else
                <div class="w-full bg-red-600 px-4 py-4 rounded relative dark:bg-red-800 text-center shadow-2xl" role="alert">
                    <span class="block sm:inline text-gray-100">Geen inschrijvingen gevonden.</span>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
