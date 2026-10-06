<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Les aanvragen') }}
        </h2>
    </x-slot>

    @php
        $input = 'bg-gray-200 text-gray-900 p-2 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm';
        $label = 'block text-sm font-medium text-gray-700 dark:text-gray-300';
        $legend = 'text-base font-semibold text-gray-900 dark:text-gray-100 mb-2';
    @endphp

    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 py-6">
        <x-flash />

        @if ($registrations->isEmpty())
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 text-gray-700 dark:text-gray-300">
                <p>Je hebt op dit moment geen actief lespakket met lessen over, dus je kunt geen les aanvragen.</p>
                <p class="mt-2">Neem contact op met de rijschool om een (nieuw) lespakket af te nemen.</p>
                <a href="{{ route('student.lessons.index') }}" class="inline-block mt-4 text-indigo-600 dark:text-indigo-400 hover:underline">Terug naar mijn lessen</a>
            </div>
        @else
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <p class="mb-6 text-sm text-gray-600 dark:text-gray-400">
                        Kies wanneer je les wilt hebben. Een instructeur bekijkt je aanvraag en plant de les in; je ziet de status bij
                        <a href="{{ route('student.lessons.index') }}" class="text-indigo-600 dark:text-indigo-400 underline">Mijn lessen</a>.
                    </p>

                    <form method="POST" action="{{ route('student.lessons.store') }}">
                        @csrf

                        <fieldset class="mb-6">
                            <legend class="{{ $legend }}">Lespakket</legend>
                            <label for="registration_id" class="{{ $label }}">Lespakket*</label>
                            <select name="registration_id" id="registration_id" required class="{{ $input }}">
                                @foreach ($registrations as $registration)
                                    <option value="{{ $registration->id }}" @selected(old('registration_id') == $registration->id)>
                                        {{ $registration->package->type }} - nog {{ $registration->remainingLessons() }} {{ $registration->remainingLessons() == 1 ? 'les' : 'lessen' }}
                                    </option>
                                @endforeach
                            </select>
                        </fieldset>

                        <fieldset class="mb-6">
                            <legend class="{{ $legend }}">Wanneer?</legend>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <label for="start_date" class="{{ $label }}">Datum*</label>
                                    <input type="date" name="start_date" id="start_date" required class="{{ $input }}"
                                        min="{{ today()->addDay()->format('Y-m-d') }}"
                                        value="{{ old('start_date', today()->addDay()->format('Y-m-d')) }}">
                                </div>
                                <div>
                                    <label for="start_time" class="{{ $label }}">Begintijd*</label>
                                    <input type="time" name="start_time" id="start_time" required class="{{ $input }}"
                                        value="{{ old('start_time', '09:00') }}">
                                </div>
                                <div>
                                    <label for="end_time" class="{{ $label }}">Eindtijd*</label>
                                    <input type="time" name="end_time" id="end_time" required class="{{ $input }}"
                                        value="{{ old('end_time', '10:00') }}">
                                </div>
                            </div>
                        </fieldset>

                        <x-pickup-address-fields :address="$contact" />

                        <fieldset class="mb-6">
                            <legend class="{{ $legend }}">Les</legend>
                            <label for="goal" class="{{ $label }}">Waar wil je aan werken?*</label>
                            <input type="text" name="goal" id="goal" list="goal-options" required maxlength="255" class="{{ $input }}"
                                value="{{ old('goal') }}" placeholder="Bijvoorbeeld: Rotondes">
                            <datalist id="goal-options">
                                @foreach ($goals as $goal)
                                    <option value="{{ $goal }}">
                                @endforeach
                            </datalist>

                            <div class="mt-4">
                                <label for="student_comment" class="{{ $label }}">Opmerking voor de instructeur</label>
                                <textarea name="student_comment" id="student_comment" rows="2" maxlength="1000" class="{{ $input }}">{{ old('student_comment') }}</textarea>
                            </div>
                        </fieldset>

                        <div class="flex justify-between items-center">
                            <a href="{{ route('student.lessons.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">Annuleren</a>
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500">
                                Aanvraag versturen
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
