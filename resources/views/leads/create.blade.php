<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            New Lead
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-4 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6">
                <form method="POST" action="{{ route('leads.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Name <span class="text-red-500">*</span>
                        </label>
                        <input id="name" name="name" type="text" value="{{ old('name') }}" required
                               class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm
                                      focus:border-indigo-500 focus:ring-indigo-500">
                        @error('name')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Phone <span class="text-red-500">*</span>
                        </label>
                        <input id="phone" name="phone" type="text" value="{{ old('phone') }}" required
                               class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm
                                      focus:border-indigo-500 focus:ring-indigo-500">
                        @error('phone')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Email (optional)
                        </label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}"
                               class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm
                                      focus:border-indigo-500 focus:ring-indigo-500">
                        @error('email')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Address
                        </label>
                        <textarea id="address" name="address" rows="2"
                                  class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm
                                         focus:border-indigo-500 focus:ring-indigo-500">{{ old('address') }}</textarea>
                        @error('address')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="source" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Source <span class="text-red-500">*</span>
                            </label>
                            <select id="source" name="source" required
                                    class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm
                                           focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="walk-in" {{ old('source') === 'walk-in' ? 'selected' : '' }}>Walk-in</option>
                                <option value="phone" {{ old('source') === 'phone' ? 'selected' : '' }}>Phone Call</option>
                                <option value="whatsapp" {{ old('source') === 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                                <option value="website" {{ old('source') === 'website' ? 'selected' : '' }}>Website</option>
                                <option value="social" {{ old('source') === 'social' ? 'selected' : '' }}>Social Media</option>
                                <option value="referral" {{ old('source') === 'referral' ? 'selected' : '' }}>Referral</option>
                                <option value="other" {{ old('source') === 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('source')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="priority" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Priority <span class="text-red-500">*</span>
                            </label>
                            <select id="priority" name="priority" required
                                    class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm
                                           focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="medium" {{ old('priority', 'medium') === 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>High</option>
                                <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Low</option>
                            </select>
                            @error('priority')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="interested_in" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Interested In <span class="text-red-500">*</span>
                            </label>
                            <select id="interested_in" name="interested_in" required
                                    class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm
                                           focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="moto" {{ old('interested_in', 'moto') === 'moto' ? 'selected' : '' }}>Motorcycle Service</option>
                                <option value="ac" {{ old('interested_in') === 'ac' ? 'selected' : '' }}>AC Service</option>
                                <option value="both" {{ old('interested_in') === 'both' ? 'selected' : '' }}>Both</option>
                            </select>
                            @error('interested_in')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="follow_up_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Follow-up Date <span class="text-xs text-gray-500">(Default: 24 hours)</span>
                            </label>
                            <input id="follow_up_date" name="follow_up_date" type="date" value="{{ old('follow_up_date', now()->addDay()->format('Y-m-d')) }}"
                                   class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm
                                          focus:border-indigo-500 focus:ring-indigo-500">
                            @error('follow_up_date')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Notes
                        </label>
                        <textarea id="notes" name="notes" rows="3"
                                  class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm
                                         focus:border-indigo-500 focus:ring-indigo-500"
                                  placeholder="Vehicle details, requirements, conversation notes...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <a href="{{ route('leads.index') }}"
                           class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:hover:text-gray-100">
                            Cancel
                        </a>
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md
                                       font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700
                                       focus:outline-none">
                            Save Lead
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
