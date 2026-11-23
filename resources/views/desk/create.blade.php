@include('desk.partials.head')

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <a href="{{ route('desk.index') }}" class="text-sm text-indigo-600 font-medium">&larr; {{ __('My jobs') }}</a>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Log a new job') }}</h2>
            </div>
            @include('desk.partials.lang')
        </div>
    </x-slot>

    <div class="py-4 sm:py-6">
        <div class="max-w-xl mx-auto px-3 sm:px-6">
            @if ($errors->any())
                <div class="mb-4 px-4 py-3 bg-red-100 text-red-800 rounded-lg text-sm">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('desk.store') }}" class="bg-white rounded-xl shadow-sm p-4 space-y-4">
                @csrf

                <fieldset>
                    <legend class="text-sm font-medium text-gray-700 mb-1">{{ __('Business') }}</legend>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach($types as $key => $label)
                            <label class="flex items-center gap-2 px-3 py-2 rounded-lg border text-sm cursor-pointer has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50">
                                <input type="radio" name="job_type" value="{{ $key }}" class="text-indigo-600"
                                       @checked(old('job_type', count($types) === 1 ? $key : null) === $key) required>
                                {{ __($label) }}
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="customer_name">{{ __('Customer name') }}</label>
                        <input id="customer_name" name="customer_name" value="{{ old('customer_name') }}" required maxlength="255" class="block w-full rounded-lg border-gray-300 text-base">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="customer_phone">{{ __('Phone') }}</label>
                        <input id="customer_phone" name="customer_phone" type="tel" inputmode="tel" value="{{ old('customer_phone') }}" required maxlength="20" class="block w-full rounded-lg border-gray-300 text-base">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="location">{{ __('Address') }}</label>
                    <input id="location" name="location" value="{{ old('location') }}" required maxlength="255" class="block w-full rounded-lg border-gray-300 text-base">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="title">{{ __('Work (short)') }}</label>
                    <input id="title" name="title" value="{{ old('title') }}" required maxlength="100" placeholder="{{ __('e.g. AC water leak') }}" class="block w-full rounded-lg border-gray-300 text-base">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="problem_description">{{ __('Issue and details') }}</label>
                    <textarea id="problem_description" name="problem_description" rows="4" required maxlength="2000" class="block w-full rounded-lg border-gray-300 text-base">{{ old('problem_description') }}</textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="scheduled_at">{{ __('Promised time (optional)') }}</label>
                        <input id="scheduled_at" name="scheduled_at" type="datetime-local" value="{{ old('scheduled_at') }}" class="block w-full rounded-lg border-gray-300 text-base">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="priority">{{ __('Priority') }}</label>
                        <select id="priority" name="priority" class="block w-full rounded-lg border-gray-300 text-base">
                            @foreach(\App\Models\Job::getPriorities() as $key => $label)
                                <option value="{{ $key }}" @selected(old('priority', 'normal') === $key)>{{ __($label) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <label class="flex items-center gap-2 text-sm font-medium text-gray-800">
                    <input type="checkbox" name="assign_me" value="1" @checked(old('assign_me'))>
                    {{ __('Assign this job to me') }}
                </label>
                <p class="text-xs text-gray-500 -mt-2">{{ __('Leave it unticked if someone else should take it. It will show under "Open jobs" for the team.') }}</p>

                <button class="w-full px-4 py-3 rounded-xl bg-indigo-600 text-white font-semibold">{{ __('Save job') }}</button>
            </form>
        </div>
    </div>
</x-app-layout>
