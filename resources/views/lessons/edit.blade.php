<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Les wijzigen') }}
            </h2>
            <x-lesson-status :status="$lesson->status" />
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 py-6">
        <x-flash />
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900 dark:text-gray-100">
                @if ($lesson->status === 'aangevraagd')
                    <div class="mb-6 p-4 rounded-md bg-yellow-50 dark:bg-yellow-900/40 text-sm text-yellow-800 dark:text-yellow-200">
                        Dit is een lesaanvraag van de leerling. Kies een instructeur en auto en zet de status op
                        <strong>Gepland</strong> om de les in te plannen.
                    </div>
                @endif
                <form method="POST" action="{{ route('lessons.update', $lesson) }}">
                    @method('PATCH')
                    @include('lessons._form', ['submit' => 'Opslaan'])
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
