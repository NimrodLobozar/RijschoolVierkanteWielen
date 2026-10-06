<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Les beheren') }}
            </h2>
            <x-lesson-status :status="$lesson->status" />
        </div>
    </x-slot>

    @php
        $input = 'bg-gray-200 text-gray-900 p-2 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm';
        $label = 'block text-sm font-medium text-gray-700 dark:text-gray-300';
        $legend = 'text-base font-semibold text-gray-900 dark:text-gray-100 mb-2';
        $student = $lesson->registration->student->user;
    @endphp

    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 py-6">
        <x-flash />

        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-5 mb-6 text-sm text-gray-700 dark:text-gray-300">
            <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $student->full_name }}</p>
            <p>{{ $lesson->registration->package->type }} &middot; {{ $student->contact?->email }} &middot; {{ $student->contact?->mobile }}</p>
            @if ($lesson->student_comment)
                <p class="mt-2"><strong>Opmerking van de leerling:</strong> {{ $lesson->student_comment }}</p>
            @endif
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900 dark:text-gray-100">
                <form method="POST" action="{{ route('instructor.lessons.update', $lesson) }}">
                    @csrf
                    @method('PATCH')

                    <fieldset class="mb-6">
                        <legend class="{{ $legend }}">Planning</legend>
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                            <div>
                                <label for="start_date" class="{{ $label }}">Datum*</label>
                                <input type="date" name="start_date" id="start_date" required class="{{ $input }}"
                                    value="{{ old('start_date', $lesson->start_date->format('Y-m-d')) }}">
                            </div>
                            <div>
                                <label for="start_time" class="{{ $label }}">Begintijd*</label>
                                <input type="time" name="start_time" id="start_time" required class="{{ $input }}"
                                    value="{{ old('start_time', substr($lesson->start_time, 0, 5)) }}">
                            </div>
                            <div>
                                <label for="end_time" class="{{ $label }}">Eindtijd*</label>
                                <input type="time" name="end_time" id="end_time" required class="{{ $input }}"
                                    value="{{ old('end_time', substr($lesson->end_time, 0, 5)) }}">
                            </div>
                            <div>
                                <label for="status" class="{{ $label }}">Status*</label>
                                <select name="status" id="status" required class="{{ $input }}">
                                    @foreach ($statuses as $value => $text)
                                        <option value="{{ $value }}" @selected(old('status', $lesson->status) === $value)>{{ $text }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mt-4">
                            <label for="auto_id" class="{{ $label }}">Auto*</label>
                            <select name="auto_id" id="auto_id" required class="{{ $input }}">
                                @foreach ($autos as $auto)
                                    <option value="{{ $auto->id }}" @selected(old('auto_id', $lesson->auto_id) == $auto->id)>
                                        {{ $auto->brand }} {{ $auto->model }} ({{ $auto->license_plate }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </fieldset>

                    <x-pickup-address-fields :address="$lesson->pickUpAddress" />

                    <fieldset class="mb-6">
                        <legend class="{{ $legend }}">Les</legend>
                        <label for="goal" class="{{ $label }}">Lesdoel*</label>
                        <input type="text" name="goal" id="goal" list="goal-options" required maxlength="255" class="{{ $input }}"
                            value="{{ old('goal', $lesson->goal) }}">
                        <datalist id="goal-options">
                            @foreach ($goals as $goal)
                                <option value="{{ $goal }}">
                            @endforeach
                        </datalist>

                        <div class="mt-4">
                            <label for="instructor_comment" class="{{ $label }}">Mijn opmerking / voortgang</label>
                            <textarea name="instructor_comment" id="instructor_comment" rows="3" maxlength="1000" class="{{ $input }}"
                                placeholder="Bijvoorbeeld: rotondes gingen goed, volgende les invoegen oefenen.">{{ old('instructor_comment', $lesson->instructor_comment) }}</textarea>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">De leerling ziet deze opmerking bij de les.</p>
                        </div>
                    </fieldset>

                    <div class="flex justify-between items-center">
                        <a href="{{ route('instructor.lessons.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">Terug</a>
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500">
                            Opslaan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
