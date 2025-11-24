@if(file_exists(public_path('logo.png')))
    <img src="{{ asset('logo.png') }}" alt="MicroNET Logo" {{ $attributes->merge(['class' => 'h-9 w-auto']) }}>
@else
    <span class="text-xl font-bold text-gray-900 dark:text-white">MicroNET</span>
@endif
