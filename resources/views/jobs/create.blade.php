{{-- resources/views/jobs/create.blade.php - Mobile-First Quick Job Creation --}}

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            New Job
        </h2>
    </x-slot>

    <div class="py-2 sm:py-6">
        <div class="max-w-lg mx-auto px-3 sm:px-6">
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
                <form method="POST" action="{{ route('jobs.store') }}" class="p-4 space-y-4">
                    @csrf

                    {{-- Service Type - Large touch targets --}}
                    <div x-data="{ jobType: '{{ old('job_type', 'moto') }}' }">
                        <div class="grid grid-cols-2 gap-2">
                            <label class="cursor-pointer" @click="jobType = 'moto'">
                                <input type="radio" name="job_type" value="moto" class="sr-only" x-model="jobType">
                                <div class="flex items-center justify-center p-4 border-2 rounded-xl transition-all"
                                     :class="jobType === 'moto'
                                         ? 'border-orange-500 bg-orange-100 dark:bg-orange-900/30'
                                         : 'border-gray-200 dark:border-gray-600'">
                                    <span class="text-2xl mr-2">🏍️</span>
                                    <span class="font-bold text-lg text-gray-900 dark:text-gray-100">Bike</span>
                                </div>
                            </label>
                            <label class="cursor-pointer" @click="jobType = 'ac'">
                                <input type="radio" name="job_type" value="ac" class="sr-only" x-model="jobType">
                                <div class="flex items-center justify-center p-4 border-2 rounded-xl transition-all"
                                     :class="jobType === 'ac'
                                         ? 'border-sky-500 bg-sky-100 dark:bg-sky-900/30'
                                         : 'border-gray-200 dark:border-gray-600'">
                                    <span class="text-2xl mr-2">❄️</span>
                                    <span class="font-bold text-lg text-gray-900 dark:text-gray-100">AC</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Phone - Auto-focused, large input --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Phone</label>
                        <input type="tel" name="customer_phone" id="customer_phone"
                               value="{{ old('customer_phone', $preselectedCustomer?->phone) }}"
                               class="block w-full text-xl p-4 rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500"
                               placeholder="7XXXXXX"
                               inputmode="tel"
                               autofocus
                               required>
                        @error('customer_phone')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Name --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Name</label>
                        <input type="text" name="customer_name"
                               value="{{ old('customer_name', $preselectedCustomer?->name) }}"
                               class="block w-full text-lg p-3 rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500"
                               placeholder="Customer name"
                               required>
                        @error('customer_name')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Title / Issue --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Issue</label>
                        <input type="text" name="title"
                               value="{{ old('title') }}"
                               class="block w-full text-lg p-3 rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500"
                               placeholder="AC not cooling, bike won't start...">
                    </div>

                    {{-- Location --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Location</label>
                        <input type="text" name="location"
                               value="{{ old('location') }}"
                               class="block w-full p-3 rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500"
                               placeholder="Hulhumale Phase 2, Flat 101...">
                    </div>

                    {{-- Schedule & Priority - Collapsible --}}
                    <div x-data="{ showMore: {{ old('scheduled_at') || old('priority') ? 'true' : 'false' }} }">
                        <button type="button" @click="showMore = !showMore"
                                class="flex items-center text-sm text-indigo-600 dark:text-indigo-400 font-medium">
                            <svg class="w-4 h-4 mr-1 transition-transform" :class="{ 'rotate-90': showMore }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                            Schedule & Priority
                        </button>
                        <div x-show="showMore" x-collapse class="mt-3 space-y-3">
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Date & Time</label>
                                    <input type="datetime-local" name="scheduled_at"
                                           value="{{ old('scheduled_at', $scheduledAt) }}"
                                           class="block w-full p-3 rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Priority</label>
                                    <select name="priority"
                                            class="block w-full p-3 rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm">
                                        <option value="normal" {{ old('priority', 'normal') === 'normal' ? 'selected' : '' }}>Normal</option>
                                        <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>High</option>
                                        <option value="urgent" {{ old('priority') === 'urgent' ? 'selected' : '' }}>🔴 Urgent</option>
                                        <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Low</option>
                                    </select>
                                </div>
                            </div>

                            {{-- Assign Technician --}}
                            @if($technicians->count() > 0)
                            <div>
                                <label class="block text-xs text-gray-500 mb-2">Assign to</label>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($technicians as $tech)
                                        <label class="cursor-pointer">
                                            <input type="checkbox" name="assignees[]" value="{{ $tech->id }}" class="sr-only peer"
                                                   {{ in_array($tech->id, old('assignees', [])) ? 'checked' : '' }}>
                                            <span class="inline-block px-3 py-2 rounded-lg text-sm border-2 transition-all
                                                         peer-checked:border-indigo-500 peer-checked:bg-indigo-50 dark:peer-checked:bg-indigo-900/30
                                                         border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300">
                                                {{ $tech->name }}
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Hidden fields --}}
                    <input type="hidden" name="job_date" value="{{ date('Y-m-d') }}">
                    <input type="hidden" name="job_category" value="general">

                    {{-- Sticky Submit Button --}}
                    <div class="sticky bottom-0 pt-4 pb-2 bg-white dark:bg-gray-800 -mx-4 px-4 border-t border-gray-100 dark:border-gray-700">
                        <button type="submit"
                                class="w-full py-4 px-6 bg-indigo-600 text-white text-lg font-bold rounded-xl hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 active:bg-indigo-800">
                            Create Job
                        </button>
                        <a href="{{ route('jobs.index') }}"
                           class="block text-center mt-2 py-2 text-gray-500 dark:text-gray-400 text-sm">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Auto-focus phone field on page load
        document.addEventListener('DOMContentLoaded', function() {
            const phoneInput = document.getElementById('customer_phone');
            if (phoneInput && !phoneInput.value) {
                setTimeout(() => phoneInput.focus(), 100);
            }
        });
    </script>
</x-app-layout>
