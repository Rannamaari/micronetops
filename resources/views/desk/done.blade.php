@include('desk.partials.head')

@php
    $units = \App\Services\JobDesk::unitLabels();
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <a href="{{ route('desk.index') }}" class="text-sm text-indigo-600 font-medium">&larr; {{ __('Job Desk') }}</a>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Completed jobs') }}</h2>
            </div>
            @include('desk.partials.lang')
        </div>
    </x-slot>

    <div class="py-4 sm:py-6">
        <div class="max-w-3xl mx-auto px-3 sm:px-6 space-y-4">
            <div class="grid grid-cols-2 gap-2">
                <a href="{{ route('desk.done', ['show' => 'mine']) }}"
                   class="rounded-xl px-3 py-2.5 text-center text-sm font-semibold border-2 {{ $mine ? 'border-indigo-600 bg-indigo-600 text-white' : 'border-gray-200 bg-white text-gray-800' }}">{{ __('My completed jobs') }}</a>
                <a href="{{ route('desk.done') }}"
                   class="rounded-xl px-3 py-2.5 text-center text-sm font-semibold border-2 {{ !$mine ? 'border-indigo-600 bg-indigo-600 text-white' : 'border-gray-200 bg-white text-gray-800' }}">{{ __('Everyone') }}</a>
            </div>
            <p class="text-xs text-gray-500">{{ __('Last 30 days') }} · {{ trans_choice(':count job|:count jobs', $total, ['count' => $total]) }}</p>

            @forelse($groups as $day => $jobs)
                @php
                    $d = $day !== 'unknown' ? \Carbon\Carbon::parse($day) : null;
                    $label = $d ? (($d->isToday() ? __('Today') . ' · ' : ($d->isYesterday() ? __('Yesterday') . ' · ' : '')) . $d->translatedFormat('l j M')) : __('Date not recorded');
                @endphp
                <section class="space-y-2">
                    <div class="flex items-baseline justify-between border-b border-gray-200 pb-1">
                        <h3 class="text-sm font-bold uppercase tracking-wide text-gray-800">{{ $label }}</h3>
                        <span class="text-xs text-gray-500">{{ trans_choice(':count job|:count jobs', $jobs->count(), ['count' => $jobs->count()]) }}</span>
                    </div>
                    @foreach($jobs as $job)
                        <a href="{{ route('desk.show', $job) }}" class="flex items-center justify-between gap-3 bg-white rounded-lg shadow-sm px-3 py-2 hover:bg-gray-50">
                            <div class="min-w-0">
                                <div class="text-sm font-semibold text-gray-900 truncate">
                                    <span class="text-xs text-gray-500 font-normal">#{{ $job->id }}</span> {{ $job->title ?: __('Job') . ' #' . $job->id }}
                                </div>
                                <div class="text-xs text-gray-600 truncate">
                                    {{ __($units[$job->businessUnit()] ?? $job->job_type) }} · {{ $job->customer_name }}
                                    · {{ $job->assignees->pluck('name')->join(', ') ?: __('Nobody') }}
                                </div>
                            </div>
                            <div class="flex-shrink-0 text-right text-[11px]">
                                @if($job->invoiced_at || $job->payment_status === 'paid')
                                    <span class="text-green-700 font-semibold">✓ {{ $job->payment_status === 'paid' ? __('Paid') : __('Invoice given') }}</span>
                                @else
                                    <span class="text-amber-700 font-semibold">{{ __('Invoice not given') }}</span>
                                @endif
                                @if($job->completed_at)
                                    <div class="text-gray-500">{{ $job->completed_at->translatedFormat('g:i A') }}</div>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </section>
            @empty
                <div class="bg-white rounded-xl p-8 text-center text-gray-500">{{ __('No completed jobs yet.') }}</div>
            @endforelse
        </div>
    </div>
</x-app-layout>
