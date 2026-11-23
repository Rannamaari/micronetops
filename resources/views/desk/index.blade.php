@include('desk.partials.head')

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
        <div class="max-w-3xl mx-auto px-3 sm:px-6 space-y-6">

            @if (session('success'))
                <div class="px-4 py-3 bg-green-100 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="px-4 py-3 bg-red-100 text-red-800 rounded-lg text-sm">{{ session('error') }}</div>
            @endif

            {{-- Customers waiting for a call: mine first, then the team's --}}
            @if($myCalls->isNotEmpty() || $teamJobs->isNotEmpty())
                <section class="rounded-2xl border-2 border-red-300 bg-red-50 p-4 space-y-3">
                    <div>
                        <h3 class="text-lg font-bold text-red-800">{{ __('Customers waiting for an explanation') }}</h3>
                        <p class="text-sm text-red-700">{{ __('Call these customers, say sorry and give a new time. Then log the call on the job card.') }}</p>
                    </div>

                    @foreach($myCalls as $job)
                        @include('desk.partials.job-row', ['job' => $job])
                    @endforeach

                    @if($teamJobs->isNotEmpty())
                        <div class="pt-2 text-sm font-semibold text-red-800">{{ __('Team – anyone free, please help') }}</div>
                        @foreach($teamJobs as $job)
                            @include('desk.partials.job-row', ['job' => $job])
                        @endforeach
                    @endif
                </section>
            @endif

            {{-- My jobs --}}
            <section class="space-y-3">
                <div class="flex items-baseline justify-between">
                    <h3 class="text-lg font-bold text-gray-900">{{ __('My jobs') }}</h3>
                    <span class="text-sm text-gray-500">{{ trans_choice(':count job|:count jobs', $myJobs->count(), ['count' => $myJobs->count()]) }}</span>
                </div>

                @if($counts['overdue'] || $counts['not_accepted'] || $counts['not_invoiced'])
                    <div class="flex flex-wrap gap-2 text-xs">
                        @if($counts['overdue'])
                            <span class="px-2 py-1 rounded-full bg-red-100 text-red-800 font-semibold">{{ __('Overdue') }}: {{ $counts['overdue'] }}</span>
                        @endif
                        @if($counts['not_accepted'])
                            <span class="px-2 py-1 rounded-full bg-amber-100 text-amber-800 font-semibold">{{ __('Not accepted') }}: {{ $counts['not_accepted'] }}</span>
                        @endif
                        @if($counts['not_invoiced'])
                            <span class="px-2 py-1 rounded-full bg-amber-100 text-amber-800 font-semibold">{{ __('Invoice not given') }}: {{ $counts['not_invoiced'] }}</span>
                        @endif
                    </div>
                @endif

                @forelse($myJobs as $job)
                    @include('desk.partials.job-row', ['job' => $job])
                @empty
                    <div class="bg-white rounded-xl p-8 text-center text-gray-500">
                        {{ __('No jobs assigned to you right now.') }}
                    </div>
                @endforelse
            </section>
        </div>
    </div>
</x-app-layout>
