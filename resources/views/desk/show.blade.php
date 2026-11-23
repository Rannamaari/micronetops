@include('desk.partials.head')

@use('App\Models\Job')
@use('App\Services\JobDesk')

@php
    $statuses = Job::getStatuses();
    $units = JobDesk::unitLabels();
    $methods = JobDesk::methodLabels();
    $outcomes = JobDesk::outcomeLabels();
    $explanations = JobDesk::explanationLabels();
    $scripts = JobDesk::scripts();
    $reasonLabels = JobDesk::contactReasonLabels();
    $promised = $job->promisedAt();
    $phoneDigits = preg_replace('/[^0-9]/', '', (string) $job->customer_phone);
    $translatedScripts = collect($scripts)->map(fn ($s) => __($s))->all();
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <a href="{{ route('desk.index') }}" class="text-sm text-indigo-600 font-medium">&larr; {{ __('My jobs') }}</a>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Job') }} #{{ $job->id }}</h2>
            </div>
            @include('desk.partials.lang')
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
            @if ($errors->any())
                <div class="px-4 py-3 bg-red-100 text-red-800 rounded-lg text-sm">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            {{-- Customer waiting for a call --}}
            @if($owesCall)
                <div class="rounded-2xl border-2 border-red-400 bg-red-50 p-4">
                    <div class="text-lg font-bold text-red-800">{{ __('The customer is waiting for a call') }}</div>
                    <div class="text-sm text-red-700 mt-1">
                        {{ __('Reason') }}: {{ __($reasonLabels[$callReason] ?? $callReason) }}.
                        {{ __('Call, say sorry, explain and give a new time. Then log it below.') }}
                    </div>
                    @if($phoneDigits)
                        <div class="mt-3 flex gap-2">
                            <a href="tel:{{ $phoneDigits }}" class="flex-1 text-center px-4 py-3 rounded-xl bg-red-600 text-white font-semibold">{{ __('Call now') }}</a>
                            <a href="#contact" class="flex-1 text-center px-4 py-3 rounded-xl bg-white border border-red-300 text-red-700 font-semibold">{{ __('Log the call') }}</a>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Job summary --}}
            <div class="bg-white rounded-xl shadow-sm p-4 space-y-3">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="text-xs text-gray-500">{{ __($units[$job->businessUnit()] ?? $job->job_type) }}</div>
                        <div class="text-lg font-bold text-gray-900">{{ $job->title ?: __('Job') . ' #' . $job->id }}</div>
                    </div>
                    <span class="px-2.5 py-1 rounded text-sm font-semibold text-white" style="background-color: {{ $job->status_color }}">
                        {{ __($statuses[$job->status] ?? $job->status) }}
                    </span>
                </div>

                @include('desk.partials.alerts', ['alerts' => $alerts])

                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                    <div>
                        <dt class="text-gray-500">{{ __('Promised time') }}</dt>
                        <dd class="font-medium {{ $job->isOverdue() ? 'text-red-600' : 'text-gray-900' }}">
                            {{ $promised ? $promised->translatedFormat('l j M, g:i A') : __('Not set') }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">{{ __('Assigned to') }}</dt>
                        <dd class="text-gray-900">
                            @forelse($job->assignees as $person)
                                <div>
                                    {{ $person->name }}
                                    @if($person->pivot->accepted_at)
                                        <span class="text-green-700 text-xs font-semibold">✓ {{ __('Accepted') }}</span>
                                    @else
                                        <span class="text-amber-700 text-xs font-semibold">{{ __('Not accepted yet') }}</span>
                                    @endif
                                </div>
                            @empty
                                <span class="text-red-600">{{ __('Nobody assigned') }}</span>
                            @endforelse
                        </dd>
                    </div>
                    @if($job->status === Job::STATUS_COMPLETED)
                        <div>
                            <dt class="text-gray-500">{{ __('Invoice') }}</dt>
                            <dd class="font-medium {{ $job->invoiced_at || $job->payment_status === 'paid' ? 'text-green-700' : 'text-amber-700' }}">
                                @if($job->invoiced_at)
                                    {{ __('Given to customer') }} · {{ $job->invoiced_at->translatedFormat('j M, g:i A') }}
                                @elseif($job->payment_status === 'paid')
                                    {{ __('Paid') }}
                                @else
                                    {{ __('Not given yet') }}
                                @endif
                            </dd>
                        </div>
                    @endif
                </dl>

                @if($job->problem_description)
                    <div class="text-sm">
                        <div class="text-gray-500">{{ __('Work to do') }}</div>
                        <div class="text-gray-900 whitespace-pre-line">{{ $job->problem_description }}</div>
                    </div>
                @endif
            </div>

            {{-- Customer --}}
            <div class="bg-white rounded-xl shadow-sm p-4 space-y-3">
                <div>
                    <div class="text-sm text-gray-500">{{ __('Customer') }}</div>
                    <div class="font-semibold text-gray-900">{{ $job->customer_name }}</div>
                    @if($job->customer_phone)
                        <div class="text-gray-700">{{ $job->customer_phone }}</div>
                    @endif
                    @if($job->location || $job->address)
                        <div class="text-sm text-gray-600 mt-1">{{ $job->location ?: $job->address }}</div>
                    @endif
                </div>
                @if($nameMissing && $canContact)
                    <form method="POST" action="{{ route('desk.customer-name', $job) }}" class="p-3 rounded-lg bg-amber-50 border border-amber-200">
                        @csrf
                        <label class="block text-sm font-semibold text-amber-900 mb-1" for="customer_name_fill">{{ __('Customer name not known yet – add it when you find out') }}</label>
                        <div class="flex gap-2">
                            <input id="customer_name_fill" name="customer_name" required maxlength="255" class="flex-1 min-w-0 rounded-lg border-gray-300 text-base">
                            <button class="px-4 py-2 rounded-lg bg-amber-600 text-white font-semibold">{{ __('Save') }}</button>
                        </div>
                    </form>
                @endif
                @if($phoneDigits)
                    <div class="grid grid-cols-2 gap-2">
                        <a href="tel:{{ $phoneDigits }}" class="text-center px-4 py-3 rounded-xl bg-indigo-600 text-white font-semibold">{{ __('Call') }}</a>
                        <a href="https://wa.me/{{ $phoneDigits }}" target="_blank" rel="noopener" class="text-center px-4 py-3 rounded-xl bg-green-600 text-white font-semibold">WhatsApp</a>
                    </div>
                @endif
            </div>

            {{-- Assign myself --}}
            @if($canSelfAssign)
                <form method="POST" action="{{ route('desk.take', $job) }}" class="bg-white rounded-xl shadow-sm p-4">
                    @csrf
                    @if($job->assignees->isEmpty())
                        <p class="text-sm text-red-700 font-semibold mb-2">{{ __('Nobody is on this job yet.') }}</p>
                    @endif
                    @if(!$job->scheduled_at)
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="take_time">{{ __('When will you reach the customer?') }}</label>
                        <input type="datetime-local" id="take_time" name="scheduled_at" required
                               value="{{ old('scheduled_at') }}" min="{{ now()->format('Y-m-d\TH:i') }}"
                               class="block w-full rounded-lg border-gray-300 text-base mb-3">
                    @endif
                    <button class="w-full px-4 py-4 rounded-xl bg-indigo-600 text-white text-lg font-bold">{{ __('Assign to me') }}</button>
                </form>
            @endif

            {{-- Work actions --}}
            @if($canWork)
                <div class="bg-white rounded-xl shadow-sm p-4 space-y-3">
                    <div class="font-semibold text-gray-900">{{ __('Update the job') }}</div>
                    <div class="grid grid-cols-2 gap-2">
                        @if($isAssigned && !$hasAccepted && $job->isActive())
                            <form method="POST" action="{{ route('desk.accept', $job) }}" class="col-span-2">
                                @csrf
                                <button class="w-full px-4 py-4 rounded-xl bg-indigo-600 text-white text-lg font-bold">{{ __('Accept job') }}</button>
                            </form>
                        @endif

                        @if(in_array($job->status, [Job::STATUS_NEW, Job::STATUS_SCHEDULED]))
                            <form method="POST" action="{{ route('desk.status', $job) }}">
                                @csrf
                                <input type="hidden" name="action" value="start">
                                <button class="w-full px-4 py-3 rounded-xl bg-amber-500 text-white font-semibold">{{ __('Start work') }}</button>
                            </form>
                        @endif

                        @if($job->status === Job::STATUS_IN_PROGRESS)
                            <form method="POST" action="{{ route('desk.status', $job) }}">
                                @csrf
                                <input type="hidden" name="action" value="hold">
                                <button class="w-full px-4 py-3 rounded-xl bg-red-500 text-white font-semibold">{{ __('On hold – waiting parts') }}</button>
                            </form>
                        @endif

                        @if($job->status === Job::STATUS_WAITING_PARTS)
                            <form method="POST" action="{{ route('desk.status', $job) }}">
                                @csrf
                                <input type="hidden" name="action" value="resume">
                                <button class="w-full px-4 py-3 rounded-xl bg-amber-500 text-white font-semibold">{{ __('Resume work') }}</button>
                            </form>
                        @endif

                        @if($job->isActive())
                            <form method="POST" action="{{ route('desk.status', $job) }}">
                                @csrf
                                <input type="hidden" name="action" value="complete">
                                <button class="w-full px-4 py-3 rounded-xl bg-green-600 text-white font-semibold">{{ __('Work completed') }}</button>
                            </form>
                        @endif

                        @if($job->status === Job::STATUS_COMPLETED && !$job->invoiced_at)
                            <form method="POST" action="{{ route('desk.invoiced', $job) }}" class="col-span-2">
                                @csrf
                                <button class="w-full px-4 py-3 rounded-xl bg-blue-600 text-white font-semibold">{{ __('Invoice given to customer') }}</button>
                            </form>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Customer contact log --}}
            @if($canContact)
                <div id="contact" class="bg-white rounded-xl shadow-sm p-4 {{ $owesCall ? 'ring-2 ring-red-400' : '' }}"
                     x-data="{ outcome: '{{ old('outcome', 'informed') }}', explanation: '{{ old('explanation', $callReason === 'waiting_parts' ? 'waiting_parts' : ($callReason === 'fault' ? 'fault' : 'running_late')) }}', scripts: @js($translatedScripts) }">
                    <div class="font-semibold text-gray-900">{{ __('Log customer contact') }}</div>
                    <p class="text-sm text-gray-500 mb-3">{{ __('Every call, WhatsApp or visit with the customer.') }}</p>

                    <form method="POST" action="{{ route('desk.contact', $job) }}" class="space-y-4">
                        @csrf
                        <fieldset>
                            <legend class="text-sm font-medium text-gray-700 mb-1">{{ __('How') }}</legend>
                            <div class="grid grid-cols-3 gap-2">
                                @foreach($methods as $key => $label)
                                    <label class="flex items-center justify-center gap-1 px-2 py-2 rounded-lg border text-sm cursor-pointer has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50">
                                        <input type="radio" name="method" value="{{ $key }}" class="text-indigo-600" @checked(old('method', 'call') === $key)>
                                        {{ __($label) }}
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>

                        <fieldset>
                            <legend class="text-sm font-medium text-gray-700 mb-1">{{ __('Result') }}</legend>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                @foreach($outcomes as $key => $label)
                                    <label class="flex items-center gap-2 px-3 py-2 rounded-lg border text-sm cursor-pointer has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50">
                                        <input type="radio" name="outcome" value="{{ $key }}" x-model="outcome" class="text-indigo-600">
                                        {{ __($label) }}
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>

                        <div x-show="outcome === 'rescheduled'" x-cloak>
                            <label class="block text-sm font-medium text-gray-700 mb-1" for="new_time">{{ __('New time agreed') }}</label>
                            <input type="datetime-local" id="new_time" name="new_time" value="{{ old('new_time') }}"
                                   min="{{ now()->format('Y-m-d\TH:i') }}"
                                   class="block w-full rounded-lg border-gray-300 text-base">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1" for="explanation">{{ __('What did you tell them?') }}</label>
                            <select id="explanation" name="explanation" x-model="explanation" class="block w-full rounded-lg border-gray-300 text-base">
                                @foreach($explanations as $key => $label)
                                    <option value="{{ $key }}">{{ __($label) }}</option>
                                @endforeach
                            </select>
                            <div class="mt-2 p-3 rounded-lg bg-gray-50 text-sm text-gray-700" x-show="scripts[explanation]">
                                <div class="text-xs font-semibold text-gray-500 mb-1">{{ __('You can say:') }}</div>
                                <div x-text="scripts[explanation]"></div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1" for="contact_note">{{ __('Note (optional)') }}</label>
                            <input type="text" id="contact_note" name="note" value="{{ old('note') }}" maxlength="1000" class="block w-full rounded-lg border-gray-300 text-base">
                        </div>

                        <button class="w-full px-4 py-3 rounded-xl bg-gray-900 text-white font-semibold">{{ __('Save contact') }}</button>
                    </form>
                </div>

                {{-- Remark --}}
                <div class="bg-white rounded-xl shadow-sm p-4">
                    <div class="font-semibold text-gray-900 mb-2">{{ __('Add a remark') }}</div>
                    <form method="POST" action="{{ route('desk.remark', $job) }}" class="flex gap-2">
                        @csrf
                        <input type="text" name="content" required maxlength="1000" class="flex-1 min-w-0 rounded-lg border-gray-300 text-base"
                               placeholder="{{ __('What was done, what is pending...') }}">
                        <button class="px-4 py-2 rounded-lg bg-indigo-600 text-white font-semibold">{{ __('Add') }}</button>
                    </form>
                </div>
            @endif

            {{-- Fault and cancel --}}
            @if($canWork)
                <div class="bg-white rounded-xl shadow-sm p-4 space-y-3" x-data="{ fault: {{ $errors->has('title') || $errors->has('description') ? 'true' : 'false' }}, cancel: {{ $errors->has('cancellation_reason') || $errors->has('customer_informed') ? 'true' : 'false' }} }">
                    @if($faults->isNotEmpty())
                        <div class="text-sm">
                            <div class="font-semibold text-gray-900">{{ __('Faults on this job') }}</div>
                            @foreach($faults as $fault)
                                <div class="text-gray-700">{{ $fault->ticket_number }} – {{ $fault->title }} ({{ __(ucfirst(str_replace('_', ' ', $fault->status))) }})</div>
                            @endforeach
                        </div>
                    @endif

                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" @click="fault = !fault; cancel = false" class="px-4 py-3 rounded-xl border border-red-300 text-red-700 font-semibold">{{ __('Report a fault / redo') }}</button>
                        @if($job->isActive())
                            <button type="button" @click="cancel = !cancel; fault = false" class="px-4 py-3 rounded-xl border border-gray-300 text-gray-700 font-semibold">{{ __('Cancel / customer refused') }}</button>
                        @endif
                    </div>

                    <form method="POST" action="{{ route('desk.fault', $job) }}" x-show="fault" x-cloak class="space-y-3 pt-2">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('What is the problem?') }}</label>
                            <input type="text" name="title" value="{{ old('title') }}" required maxlength="255" class="block w-full rounded-lg border-gray-300 text-base">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Details') }}</label>
                            <textarea name="description" rows="3" required maxlength="2000" class="block w-full rounded-lg border-gray-300 text-base">{{ old('description') }}</textarea>
                        </div>
                        <div class="flex gap-4 text-sm">
                            <label class="flex items-center gap-2"><input type="radio" name="priority" value="normal" @checked(old('priority', 'normal') === 'normal')> {{ __('Normal (48 hours)') }}</label>
                            <label class="flex items-center gap-2"><input type="radio" name="priority" value="urgent" @checked(old('priority') === 'urgent')> {{ __('Urgent (24 hours)') }}</label>
                        </div>
                        <button class="w-full px-4 py-3 rounded-xl bg-red-600 text-white font-semibold">{{ __('Save fault') }}</button>
                    </form>

                    @if($job->isActive())
                        <form method="POST" action="{{ route('desk.cancel', $job) }}" x-show="cancel" x-cloak class="space-y-3 pt-2"
                              x-data="{ reason: '{{ old('cancellation_reason') }}', byUs: @js(Job::cancellationReasonsByUs()), owes: @js($owesCall) }">
                            @csrf
                            <div class="space-y-1">
                                @foreach(Job::getCancellationReasons() as $key => $label)
                                    <label class="flex items-center gap-2 px-3 py-2 rounded-lg border text-sm cursor-pointer has-[:checked]:border-red-500 has-[:checked]:bg-red-50">
                                        <input type="radio" name="cancellation_reason" value="{{ $key }}" x-model="reason">
                                        {{ __($label) }}
                                    </label>
                                @endforeach
                            </div>
                            <input type="text" name="cancellation_notes" value="{{ old('cancellation_notes') }}" maxlength="500"
                                   placeholder="{{ __('Notes (optional)') }}" class="block w-full rounded-lg border-gray-300 text-base">

                            <div class="p-3 rounded-lg border-2"
                                 :class="(owes || byUs.includes(reason)) && reason !== 'customer_unreachable' ? 'border-red-400 bg-red-50' : 'border-gray-200'">
                                <label class="flex items-center gap-2 font-semibold text-gray-900">
                                    <input type="checkbox" name="customer_informed" value="1" @checked(old('customer_informed'))>
                                    {{ __('Customer has been told') }}
                                </label>
                                <p class="text-xs text-gray-600 mt-1" x-show="(owes || byUs.includes(reason)) && reason !== 'customer_unreachable'">
                                    {{ __('Required: no customer goes without an explanation.') }}
                                </p>
                                <select name="contact_method" class="mt-2 block w-full rounded-lg border-gray-300 text-sm">
                                    @foreach($methods as $key => $label)
                                        <option value="{{ $key }}">{{ __($label) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <button :disabled="!reason" class="w-full px-4 py-3 rounded-xl bg-gray-700 text-white font-semibold disabled:opacity-50">{{ __('Cancel job') }}</button>
                        </form>
                    @endif
                </div>
            @endif

            {{-- Timeline --}}
            <div class="bg-white rounded-xl shadow-sm p-4">
                <div class="font-semibold text-gray-900 mb-3">{{ __('History') }}</div>
                <div class="space-y-3">
                    @forelse($job->notes as $note)
                        @php
                            $meta = $note->metadata ?? [];
                            $dot = match ($note->type) {
                                'customer_contact' => 'bg-amber-500',
                                'status_change' => 'bg-blue-500',
                                'accepted', 'assignment' => 'bg-green-500',
                                'invoiced' => 'bg-indigo-500',
                                'fault' => 'bg-red-500',
                                default => 'bg-gray-400',
                            };
                        @endphp
                        <div class="flex gap-3">
                            <span class="mt-1.5 w-2.5 h-2.5 rounded-full flex-shrink-0 {{ $dot }}"></span>
                            <div class="min-w-0 text-sm">
                                <div class="text-gray-900">
                                    @if($note->type === 'status_change' && isset($meta['from'], $meta['to']))
                                        {{ __('Status') }}: {{ __($statuses[$meta['from']] ?? $meta['from']) }} → {{ __($statuses[$meta['to']] ?? $meta['to']) }}
                                        @if(!empty($meta['cancellation_reason']))
                                            – {{ __(Job::getCancellationReasons()[$meta['cancellation_reason']] ?? $meta['cancellation_reason']) }}
                                        @endif
                                    @elseif($note->type === 'customer_contact' && isset($meta['method'], $meta['outcome']))
                                        <span class="font-semibold">{{ __($methods[$meta['method']] ?? $meta['method']) }}: {{ __($outcomes[$meta['outcome']] ?? $meta['outcome']) }}</span>
                                        @if(!empty($meta['explanation']))
                                            – {{ __($explanations[$meta['explanation']] ?? $meta['explanation']) }}
                                        @endif
                                        @if(!empty($meta['new_time']))
                                            – {{ __('new time') }} {{ \Carbon\Carbon::parse($meta['new_time'])->translatedFormat('D j M, g:i A') }}
                                        @endif
                                        @if(!empty($meta['note']))
                                            <div class="text-gray-700">“{{ $meta['note'] }}”</div>
                                        @endif
                                    @elseif($note->type === 'accepted')
                                        {{ __('Accepted the job') }}
                                    @elseif($note->type === 'invoiced')
                                        {{ __('Invoice given to customer') }}
                                    @else
                                        {{ $note->content }}
                                    @endif
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $note->user?->name ?? __('System') }} · {{ $note->created_at->translatedFormat('j M, g:i A') }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-sm text-gray-500">{{ __('Nothing yet.') }}</div>
                    @endforelse
                </div>
            </div>

            <div class="text-center pb-6">
                <a href="{{ route('jobs.show', $job) }}" class="text-sm text-indigo-600 font-medium">{{ __('Open full job (parts, invoice, payments)') }} &rarr;</a>
            </div>
        </div>
    </div>
</x-app-layout>
