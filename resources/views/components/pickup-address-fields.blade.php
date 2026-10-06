@props(['address' => null])

@php
    $input = 'bg-gray-200 text-gray-900 p-2 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm';
    $label = 'block text-sm font-medium text-gray-700 dark:text-gray-300';
@endphp

<fieldset class="mb-6">
    <legend class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-2">Ophaaladres</legend>
    <div class="grid grid-cols-1 sm:grid-cols-6 gap-4">
        <div class="sm:col-span-3">
            <label for="street" class="{{ $label }}">Straat*</label>
            <input type="text" name="street" id="street" required maxlength="255" class="{{ $input }}"
                value="{{ old('street', $address?->street) }}">
        </div>
        <div class="sm:col-span-2">
            <label for="house_number" class="{{ $label }}">Huisnummer*</label>
            <input type="text" name="house_number" id="house_number" required maxlength="10" class="{{ $input }}"
                value="{{ old('house_number', $address?->house_number) }}">
        </div>
        <div class="sm:col-span-1">
            <label for="addition" class="{{ $label }}">Toev.</label>
            <input type="text" name="addition" id="addition" maxlength="10" class="{{ $input }}"
                value="{{ old('addition', $address?->addition) }}">
        </div>
        <div class="sm:col-span-2">
            <label for="postal_code" class="{{ $label }}">Postcode*</label>
            <input type="text" name="postal_code" id="postal_code" required maxlength="10" class="{{ $input }}"
                value="{{ old('postal_code', $address?->postal_code) }}">
        </div>
        <div class="sm:col-span-4">
            <label for="city" class="{{ $label }}">Plaats*</label>
            <input type="text" name="city" id="city" required maxlength="255" class="{{ $input }}"
                value="{{ old('city', $address?->city) }}">
        </div>
    </div>
</fieldset>
