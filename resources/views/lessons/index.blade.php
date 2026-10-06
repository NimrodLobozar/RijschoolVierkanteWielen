<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap gap-4 justify-between items-center">
            <div class="flex items-center gap-3">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ __('Lessen') }}
                </h2>
                @if ($openRequests > 0)
                    <a href="{{ route('lessons.index', ['status' => 'aangevraagd', 'periode' => 'alles']) }}"
                        class="bg-yellow-100 text-yellow-800 dark:bg-yellow-900/60 dark:text-yellow-200 text-xs font-medium py-1 px-2 rounded-xl hover:underline">
                        {{ $openRequests }} {{ $openRequests === 1 ? 'open aanvraag' : 'open aanvragen' }}
                    </a>
                @endif
            </div>
            <a href="{{ route('lessons.create') }}"
                class="bg-green-600 text-white px-5 py-2.5 rounded-lg transition duration-300 hover:bg-green-700 transform hover:scale-105">
                {{ __('Les inplannen') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-flash />

            {{-- Filters --}}
            <form method="GET" action="{{ route('lessons.index') }}"
                class="bg-white dark:bg-gray-800 shadow-md sm:rounded-lg p-4 mb-6 grid grid-cols-1 sm:grid-cols-4 gap-4 items-end">
                @php $select = 'bg-gray-200 text-gray-900 p-2 mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm'; @endphp
                <div>
                    <label for="periode" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Periode</label>
                    <select name="periode" id="periode" class="{{ $select }}">
                        <option value="komend" @selected($period === 'komend')>Vandaag en later</option>
                        <option value="verleden" @selected($period === 'verleden')>Verleden</option>
                        <option value="alles" @selected($period === 'alles')>Alles</option>
                    </select>
                </div>
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                    <select name="status" id="status" class="{{ $select }}">
                        <option value="">Alle statussen</option>
                        @foreach ($statuses as $value => $text)
                            <option value="{{ $value }}" @selected(request('status') === $value)>{{ $text }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="instructor_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Instructeur</label>
                    <select name="instructor_id" id="instructor_id" class="{{ $select }}">
                        <option value="">Alle instructeurs</option>
                        @foreach ($instructors as $instructor)
                            <option value="{{ $instructor->id }}" @selected(request('instructor_id') == $instructor->id)>{{ $instructor->user->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-500">Filteren</button>
                    <a href="{{ route('lessons.index') }}" class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300 hover:underline">Wissen</a>
                </div>
            </form>

            @if ($lessons->count() > 0)
                {{-- Desktop --}}
                <div class="bg-white dark:bg-gray-800 overflow-x-auto shadow-md sm:rounded-lg hidden md:block">
                    <table class="w-full min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                @foreach (['Datum', 'Tijd', 'Leerling', 'Instructeur', 'Auto', 'Lesdoel', 'Status', 'Acties'] as $column)
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">{{ $column }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                            @foreach ($lessons as $lesson)
                                <tr class="hover:bg-gray-100 dark:hover:bg-gray-700 text-sm">
                                    <td class="px-4 py-3 whitespace-nowrap text-gray-900 dark:text-gray-100">{{ $lesson->start_date->format('d-m-Y') }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-gray-500 dark:text-gray-400">{{ $lesson->time_range }}</td>
                                    <td class="px-4 py-3 text-blue-700 dark:text-blue-500 font-medium">
                                        {{ $lesson->registration?->student?->user?->full_name ?? 'Onbekend' }}
                                        <div class="text-xs text-gray-500 dark:text-gray-400 font-normal">{{ $lesson->pickUpAddress?->full_address }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $lesson->instructor?->user?->full_name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400">
                                        {{ $lesson->auto ? $lesson->auto->brand . ' ' . $lesson->auto->model : '-' }}
                                        @if ($lesson->auto)
                                            <div class="text-xs">{{ $lesson->auto->license_plate }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $lesson->goal }}</td>
                                    <td class="px-4 py-3"><x-lesson-status :status="$lesson->status" /></td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        @include('lessons._actions')
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Mobiel --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 md:hidden">
                    @foreach ($lessons as $lesson)
                        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-5 flex flex-col space-y-2 text-sm">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                        {{ $lesson->registration?->student?->user?->full_name ?? 'Onbekend' }}
                                    </h3>
                                    <p class="text-gray-500 dark:text-gray-400">{{ $lesson->start_date->format('d-m-Y') }} &middot; {{ $lesson->time_range }}</p>
                                </div>
                                <x-lesson-status :status="$lesson->status" />
                            </div>
                            <p class="text-gray-600 dark:text-gray-300">{{ $lesson->goal }}</p>
                            <p class="text-gray-500 dark:text-gray-400">
                                Instructeur: {{ $lesson->instructor?->user?->full_name ?? '-' }}<br>
                                Auto: {{ $lesson->auto ? $lesson->auto->brand . ' ' . $lesson->auto->model : '-' }}<br>
                                Ophalen: {{ $lesson->pickUpAddress?->full_address ?? '-' }}
                            </p>
                            <div class="pt-2">@include('lessons._actions')</div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6">{{ $lessons->links() }}</div>
            @else
                <div class="w-full bg-red-600 px-4 py-4 rounded relative dark:bg-red-800 text-center shadow-2xl" role="alert">
                    <span class="block sm:inline text-gray-100">Geen lessen gevonden voor deze selectie.</span>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
