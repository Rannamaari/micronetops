<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Edit User: {{ $user->name }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto sm:px-4 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                @if ($errors->any())
                    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md p-3 text-sm text-red-600 dark:text-red-400 mb-4">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('users.update', $user) }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        @error('name')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Email <span class="text-red-500">*</span>
                        </label>
                        <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        @error('email')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            New Password (leave blank to keep current)
                        </label>
                        <input type="password" name="password" id="password"
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        @error('password')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Confirm New Password
                        </label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    </div>

                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Role <span class="text-red-500">*</span>
                        </label>
                        <select name="role" id="role" required
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="">Select a role</option>
                            <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin - Full access, can delete anything</option>
                            <option value="manager" {{ old('role', $user->role) === 'manager' ? 'selected' : '' }}>Manager - Can do everything except delete</option>
                            <option value="mechanic" {{ old('role', $user->role) === 'mechanic' ? 'selected' : '' }}>Mechanic - Can manage customers, jobs, and expenses</option>
                            <option value="cashier" {{ old('role', $user->role) === 'cashier' ? 'selected' : '' }}>Cashier - Dashboard and reports only</option>
                            <option value="hr" {{ old('role', $user->role) === 'hr' ? 'selected' : '' }}>HR - Employees, payroll, and loans management only</option>
                            <option value="customer" {{ old('role', $user->role) === 'customer' ? 'selected' : '' }}>Customer - Rattehin access only</option>
                        </select>
                        @error('role')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Premium Features Section -->
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-4 mt-4">
                        <h3 class="text-md font-semibold text-gray-800 dark:text-gray-200 mb-3">Premium Subscription (MVR 15/month)</h3>

                        <div class="flex items-center mb-4">
                            <input type="checkbox" name="is_premium" id="is_premium" value="1"
                                   {{ old('is_premium', $user->is_premium) ? 'checked' : '' }}
                                   class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600">
                            <label for="is_premium" class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Enable Premium Features
                            </label>
                        </div>

                        @if($user->isPremium() && $user->premium_expires_at)
                            <p class="text-xs text-gray-600 dark:text-gray-400 mb-3">
                                Active until: <strong>{{ $user->premium_expires_at->format('M d, Y') }}</strong>
                            </p>
                        @endif

                        <div id="premium-features" class="ml-6 space-y-2">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Select features to enable:</p>

                            <div class="flex items-center">
                                <input type="checkbox" name="premium_features[]" value="bill_upload" id="feature_bill_upload"
                                       {{ in_array('bill_upload', old('premium_features', $user->premium_features ?? [])) ? 'checked' : '' }}
                                       class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600">
                                <label for="feature_bill_upload" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                    Bill Upload (OCR Scanning) - Storage intensive
                                </label>
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" name="premium_features[]" value="bill_sharing" id="feature_bill_sharing"
                                       {{ in_array('bill_sharing', old('premium_features', $user->premium_features ?? [])) ? 'checked' : '' }}
                                       class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600">
                                <label for="feature_bill_sharing" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                    Share Bills (WhatsApp, Viber, Facebook)
                                </label>
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" name="premium_features[]" value="expense_tracking" id="feature_expense_tracking"
                                       {{ in_array('expense_tracking', old('premium_features', $user->premium_features ?? [])) ? 'checked' : '' }}
                                       class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600">
                                <label for="feature_expense_tracking" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                    Personal Expense Tracking (Coming Soon)
                                </label>
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" name="premium_features[]" value="advanced_reports" id="feature_advanced_reports"
                                       {{ in_array('advanced_reports', old('premium_features', $user->premium_features ?? [])) ? 'checked' : '' }}
                                       class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600">
                                <label for="feature_advanced_reports" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                    Advanced Reports & Analytics (Coming Soon)
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-3 pt-4">
                        <button type="submit"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-semibold hover:bg-indigo-700 focus:outline-none">
                            Update User
                        </button>
                        <a href="{{ route('users.index') }}"
                           class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md text-sm font-semibold hover:bg-gray-300 focus:outline-none dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

