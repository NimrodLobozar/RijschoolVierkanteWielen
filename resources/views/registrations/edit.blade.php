<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Inschrijving wijzigen') }}
        </h2>
    </x-slot>

    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 py-6">
        <x-flash />
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900 dark:text-gray-100">
                <form method="POST" action="{{ route('registrations.update', $registration) }}">
                    @method('PATCH')
                    @include('registrations._form', ['submit' => 'Opslaan'])
                </form>
            </div>
        </div>

        <form method="POST" action="{{ route('registrations.destroy', $registration) }}" class="mt-6 text-right"
            onsubmit="return confirm('Weet u zeker dat u deze inschrijving wilt verwijderen?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-sm text-red-600 hover:text-red-500 hover:underline">Inschrijving verwijderen</button>
        </form>
    </div>
</x-app-layout>
