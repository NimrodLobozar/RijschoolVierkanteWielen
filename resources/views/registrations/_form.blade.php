@php
    $input = 'bg-gray-200 text-gray-900 p-2 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm';
    $label = 'block text-sm font-medium text-gray-700 dark:text-gray-300';
@endphp

@csrf

<div class="mb-4">
    <label for="student_id" class="{{ $label }}">Leerling*</label>
    <select name="student_id" id="student_id" required class="{{ $input }}">
        <option value="">Kies een leerling...</option>
        @foreach ($students as $student)
            <option value="{{ $student->id }}" @selected(old('student_id', $registration->student_id) == $student->id)>
                {{ $student->user->full_name }} ({{ $student->relation_number }})
            </option>
        @endforeach
    </select>
</div>

<div class="mb-4">
    <label for="package_id" class="{{ $label }}">Lespakket*</label>
    <select name="package_id" id="package_id" required class="{{ $input }}">
        <option value="">Kies een lespakket...</option>
        @foreach ($packages as $package)
            <option value="{{ $package->id }}" @selected(old('package_id', $registration->package_id) == $package->id)>
                {{ $package->type }} - {{ $package->lesson_count }} {{ $package->lesson_count == 1 ? 'les' : 'lessen' }}
                (€{{ number_format($package->price_per_lesson, 2, ',', '.') }} per les)
            </option>
        @endforeach
    </select>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
    <div>
        <label for="start_date" class="{{ $label }}">Startdatum*</label>
        <input type="date" name="start_date" id="start_date" required class="{{ $input }}"
            value="{{ old('start_date', $registration->start_date?->format('Y-m-d')) }}">
    </div>
    <div>
        <label for="end_date" class="{{ $label }}">Einddatum</label>
        <input type="date" name="end_date" id="end_date" class="{{ $input }}"
            value="{{ old('end_date', $registration->end_date?->format('Y-m-d')) }}">
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Leeg laten voor geen einddatum.</p>
    </div>
</div>

<div class="mb-4">
    <label for="is_active" class="flex items-center">
        <input type="checkbox" name="is_active" id="is_active" value="1" @checked(old('is_active', $registration->is_active))
            class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
        <span class="ml-2 text-sm text-gray-600 dark:text-gray-300">Actief (de leerling kan lessen aanvragen)</span>
    </label>
</div>

<div class="mb-4">
    <label for="note" class="{{ $label }}">Opmerking</label>
    <textarea name="note" id="note" rows="2" maxlength="1000" class="{{ $input }}">{{ old('note', $registration->note) }}</textarea>
</div>

<div class="flex justify-between items-center">
    <a href="{{ route('registrations.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">Annuleren</a>
    <button type="submit"
        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
        {{ $submit }}
    </button>
</div>
