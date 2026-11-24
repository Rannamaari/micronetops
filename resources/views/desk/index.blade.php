@include('desk.partials.head')

@php
    $tabs = [
        'mine' => __('My jobs'),
        'all' => __('All jobs'),
        'open' => __('Open jobs'),
    ];
    $emptyText = [
        'mine' => __('No jobs assigned to you right now.'),
        'all' => __('No open jobs.'),
        'open' => __('Every job has someone on it.'),
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Job Desk') }}</h2>
            <div class="flex items-center gap-2">
                @if(auth()->user()->canManageJobDesk())
                    <a href="{{ route('desk.board') }}" class="px-3 py-1.5 rounded-lg bg-gray-900 text-white text-sm font-medium">{{ __('Admin board') }}</a>
                @endif
                @include('desk.partials.lang')
            </div>
        </div>
    </x-slot>

    <div class="py-4 sm:py-6">
        <div class="max-w-3xl mx-auto px-3 sm:px-6 space-y-4">

            @if (session('success'))
                <div class="px-4 py-3 bg-green-100 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="px-4 py-3 bg-red-100 text-red-800 rounded-lg text-sm">{{ session('error') }}</div>
            @endif

            {{-- Big, obvious main action --}}
            <a href="{{ route('desk.create') }}" class="flex items-center justify-center gap-2 w-full px-4 py-3 rounded-xl bg-indigo-600 text-white text-base font-bold shadow-sm">
                <span class="text-xl leading-none">+</span> {{ __('New job') }}
            </a>

            {{-- The one thing nobody may miss --}}
            @if($callJobs->isNotEmpty())
                <section class="rounded-xl border-2 border-red-300 bg-red-50 p-3 space-y-2">
                    <div>
                        <h3 class="text-base font-bold text-red-800">{{ __('Customers waiting for an explanation') }}</h3>
                        <p class="text-sm text-red-700">{{ __('Call these customers, say sorry and give a new time. Then log the call on the job card.') }}</p>
                    </div>
                    @foreach($callJobs as $job)
                        @include('desk.partials.job-row', ['job' => $job])
                    @endforeach
                </section>
            @endif

            {{-- Three simple tabs --}}
            <nav class="grid grid-cols-3 gap-2" aria-label="{{ __('Show') }}">
                @foreach($tabs as $key => $label)
                    <a href="{{ route('desk.index', ['show' => $key]) }}"
                       class="flex flex-col items-center justify-center rounded-xl px-2 py-2.5 text-center border-2 {{ $tab === $key ? 'border-indigo-600 bg-indigo-600 text-white' : 'border-gray-200 bg-white text-gray-800' }}"
                       @if($tab === $key) aria-current="page" @endif>
                        <span class="text-xl font-bold leading-none">{{ $tabCounts[$key] }}</span>
                        <span class="mt-1 text-sm font-semibold leading-tight">{{ $label }}</span>
                    </a>
                @endforeach
            </nav>

            @if($tab === 'open' && $tabCounts['open'] > 0)
                <p class="text-sm text-indigo-800 bg-indigo-50 rounded-lg px-3 py-2">{{ __('Open a job and press "Assign to me" to take it.') }}</p>
            @endif

            {{-- Jobs grouped by day --}}
            @forelse($groups as $key => $jobs)
                @php
                    if ($key === 'late') {
                        $heading = __('Late'); $style = 'text-red-700'; $timeOnly = false;
                    } elseif ($key === 'none') {
                        $heading = __('No time set'); $style = 'text-gray-600'; $timeOnly = true;
                    } else {
                        $day = \Carbon\Carbon::parse($key);
                        $name = $day->isToday() ? __('Today') : ($day->isTomorrow() ? __('Tomorrow') : null);
                        $heading = ($name ? $name . ' · ' : '') . $day->translatedFormat('l j M');
                        $style = $day->isToday() ? 'text-indigo-800' : 'text-gray-800';
                        $timeOnly = true;
                    }
                @endphp
                <section class="space-y-2">
                    <div class="flex items-baseline justify-between border-b border-gray-200 pb-1">
                        <h3 class="text-sm font-bold uppercase tracking-wide {{ $style }}">{{ $heading }}</h3>
                        <span class="text-xs text-gray-500">{{ trans_choice(':count job|:count jobs', $jobs->count(), ['count' => $jobs->count()]) }}</span>
                    </div>
                    @foreach($jobs as $job)
                        @include('desk.partials.job-row', ['job' => $job, 'timeOnly' => $timeOnly])
                    @endforeach
                </section>
            @empty
                <div class="bg-white rounded-xl p-8 text-center text-gray-500">{{ $emptyText[$tab] }}</div>
            @endforelse

            {{-- Finished work lives on its own page --}}
            <a href="{{ route('desk.done') }}" class="flex items-center justify-center gap-2 w-full px-4 py-3 rounded-xl border-2 border-gray-300 bg-white text-gray-800 font-semibold">
                <span class="text-green-600">✓</span> {{ __('Completed jobs') }}
            </a>
        </div>
    </div>
</x-app-layout>
