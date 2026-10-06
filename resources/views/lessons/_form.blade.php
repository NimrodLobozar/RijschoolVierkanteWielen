@php
    $input = 'bg-gray-200 text-gray-900 p-2 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm';
    $label = 'block text-sm font-medium text-gray-700 dark:text-gray-300';
    $legend = 'text-base font-semibold text-gray-900 dark:text-gray-100 mb-2';
@endphp

@csrf

<fieldset class="mb-6">
    <legend class="{{ $legend }}">Leerling en lespakket</legend>
    <label for="registration_id" class="{{ $label }}">Inschrijving*</label>
    <select name="registration_id" id="registration_id" required class="{{ $input }}">
        <option value="">Kies een inschrijving...</option>
        @foreach ($registrations as $registration)
            @php $contact = $registration->student?->user?->contact; @endphp
            <option value="{{ $registration->id }}" @selected(old('registration_id', $lesson->registration_id) == $registration->id)
                data-street="{{ $contact?->street }}" data-house-number="{{ $contact?->house_number }}"
                data-addition="{{ $contact?->addition }}" data-postal-code="{{ $contact?->postal_code }}"
                data-city="{{ $contact?->city }}">
                {{ $registration->label }} - nog {{ $registration->remainingLessons($lesson->id) }} van {{ $registration->package->lesson_count }} lessen
                {{ $registration->is_active ? '' : '(inactief)' }}
            </option>
        @endforeach
    </select>
    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
        Staat de leerling er niet bij? Maak eerst een <a href="{{ route('registrations.create') }}" class="text-indigo-600 dark:text-indigo-400 underline">inschrijving</a> aan.
    </p>
</fieldset>

<fieldset class="mb-6">
    <legend class="{{ $legend }}">Planning</legend>
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div>
            <label for="start_date" class="{{ $label }}">Datum*</label>
            <input type="date" name="start_date" id="start_date" required class="{{ $input }}"
                value="{{ old('start_date', $lesson->start_date?->format('Y-m-d')) }}">
        </div>
        <div>
            <label for="start_time" class="{{ $label }}">Begintijd*</label>
            <input type="time" name="start_time" id="start_time" required class="{{ $input }}"
                value="{{ old('start_time', substr((string) $lesson->start_time, 0, 5)) }}">
        </div>
        <div>
            <label for="end_time" class="{{ $label }}">Eindtijd*</label>
            <input type="time" name="end_time" id="end_time" required class="{{ $input }}"
                value="{{ old('end_time', substr((string) $lesson->end_time, 0, 5)) }}">
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
</fieldset>

<fieldset class="mb-6">
    <legend class="{{ $legend }}">Instructeur en auto</legend>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label for="instructor_id" class="{{ $label }}">Instructeur</label>
            <select name="instructor_id" id="instructor_id" class="{{ $input }}">
                <option value="">Nog niet gekozen</option>
                @foreach ($instructors as $instructor)
                    <option value="{{ $instructor->id }}" @selected(old('instructor_id', $lesson->instructor_id) == $instructor->id)>
                        {{ $instructor->user->full_name }} {{ $instructor->is_active ? '' : '(inactief)' }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="auto_id" class="{{ $label }}">Auto</label>
            <select name="auto_id" id="auto_id" class="{{ $input }}">
                <option value="">Nog niet gekozen</option>
                @foreach ($autos as $auto)
                    <option value="{{ $auto->id }}" @selected(old('auto_id', $lesson->auto_id) == $auto->id)>
                        {{ $auto->brand }} {{ $auto->model }} ({{ $auto->license_plate }}){{ $auto->fuel === 'electric' ? ' - elektrisch' : '' }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Verplicht bij de status Gepland of Voltooid.</p>
</fieldset>

<x-pickup-address-fields :address="$lesson->pickUpAddress" />

<fieldset class="mb-6">
    <legend class="{{ $legend }}">Les</legend>
    <label for="goal" class="{{ $label }}">Lesdoel*</label>
    <input type="text" name="goal" id="goal" list="goal-options" required maxlength="255" class="{{ $input }}"
        value="{{ old('goal', $lesson->goal) }}" placeholder="Bijvoorbeeld: Rotondes">
    <datalist id="goal-options">
        @foreach ($goals as $goal)
            <option value="{{ $goal }}">
        @endforeach
    </datalist>

    @if ($lesson->student_comment)
        <div class="mt-4">
            <span class="{{ $label }}">Opmerking van de leerling</span>
            <p class="mt-1 p-2 rounded-md bg-gray-100 dark:bg-gray-700 text-sm text-gray-800 dark:text-gray-200">{{ $lesson->student_comment }}</p>
        </div>
    @endif

    <div class="mt-4">
        <label for="instructor_comment" class="{{ $label }}">Opmerking van de instructeur</label>
        <textarea name="instructor_comment" id="instructor_comment" rows="2" maxlength="1000" class="{{ $input }}">{{ old('instructor_comment', $lesson->instructor_comment) }}</textarea>
    </div>
    <div class="mt-4">
        <label for="comment" class="{{ $label }}">Interne opmerking</label>
        <textarea name="comment" id="comment" rows="2" maxlength="1000" class="{{ $input }}">{{ old('comment', $lesson->comment) }}</textarea>
    </div>
</fieldset>

<div class="flex justify-between items-center">
    <a href="{{ route('lessons.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">Annuleren</a>
    <button type="submit"
        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
        {{ $submit }}
    </button>
</div>

<script>
    // Vul het ophaaladres met het adres van de leerling als het nog leeg is
    document.getElementById('registration_id').addEventListener('change', function () {
        const option = this.options[this.selectedIndex];
        const fields = { street: 'street', house_number: 'houseNumber', addition: 'addition', postal_code: 'postalCode', city: 'city' };
        const isEmpty = ['street', 'house_number', 'postal_code', 'city'].every(id => document.getElementById(id).value.trim() === '');
        if (!isEmpty) return;
        for (const [id, key] of Object.entries(fields)) {
            document.getElementById(id).value = option.dataset[key] || '';
        }
    });
</script>
