<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Inschrijving toevoegen') }}
        </h2>
    </x-slot>

    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 py-6">
        <x-flash />
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900 dark:text-gray-100">
                <form method="POST" action="{{ route('registrations.store') }}">
                    @include('registrations._form', ['submit' => 'Toevoegen'])
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
