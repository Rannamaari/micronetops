<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            New Customer
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-4 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6">
                <form method="POST" action="{{ route('customers.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Name
                        </label>
                        <input id="name" name="name" type="text" value="{{ old('name') }}"
                               class="block w-full rounded-md border-gray-300 shadow-sm text-sm
                                      focus:border-indigo-500 focus:ring-indigo-500">
                        @error('name')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Phone
                        </label>
                        <input id="phone" name="phone" type="text" value="{{ old('phone') }}"
                               class="block w-full rounded-md border-gray-300 shadow-sm text-sm
                                      focus:border-indigo-500 focus:ring-indigo-500">
                        @error('phone')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Email (optional)
                        </label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}"
                               class="block w-full rounded-md border-gray-300 shadow-sm text-sm
                                      focus:border-indigo-500 focus:ring-indigo-500">
                        @error('email')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Address
                        </label>
                        <input id="address" name="address" type="text" value="{{ old('address') }}"
                               class="block w-full rounded-md border-gray-300 shadow-sm text-sm
                                      focus:border-indigo-500 focus:ring-indigo-500">
                        @error('address')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Category
                        </label>
                        <select id="category" name="category"
                                class="block w-full rounded-md border-gray-300 shadow-sm text-sm
                                       focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="moto" {{ old('category') === 'moto' ? 'selected' : '' }}>Motorcycle</option>
                            <option value="ac" {{ old('category') === 'ac' ? 'selected' : '' }}>AC Service</option>
                            <option value="both" {{ old('category') === 'both' ? 'selected' : '' }}>Both</option>
                        </select>
                        @error('category')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Notes
                        </label>
                        <textarea id="notes" name="notes" rows="3"
                                  class="block w-full rounded-md border-gray-300 shadow-sm text-sm
                                         focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <a href="{{ route('customers.index') }}"
                           class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:hover:text-gray-100">
                            Cancel
                        </a>
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md
                                       font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700
                                       focus:outline-none">
                            Save Customer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

