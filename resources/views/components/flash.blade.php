@if (session('success'))
    <div class="bg-green-500 text-white p-4 rounded mb-4 flex items-center" role="status">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
        {{ session('success') }}
    </div>
@endif

@if ($errors->any())
    <div class="bg-red-500 text-white p-4 rounded mb-4" role="alert">
        <div class="flex items-center font-semibold">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            Er ging iets mis:
        </div>
        <ul class="list-disc ml-12 mt-1 text-sm">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
