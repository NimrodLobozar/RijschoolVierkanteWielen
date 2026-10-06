@props(['status'])

@php
    $colors = [
        'aangevraagd' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/60 dark:text-yellow-200',
        'gepland' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-200',
        'voltooid' => 'bg-green-100 text-green-800 dark:bg-green-900/60 dark:text-green-200',
        'geannuleerd' => 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-block py-1 px-2 rounded-xl text-xs font-medium ' . ($colors[$status] ?? $colors['geannuleerd'])]) }}>
    {{ \App\Models\Lessons::STATUSES[$status] ?? ucfirst($status) }}
</span>
