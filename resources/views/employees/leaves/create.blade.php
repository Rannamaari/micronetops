<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('employees.leaves.index', $employee) }}" class="inline-flex items-center justify-center w-10 h-10 bg-gray-100 dark:bg-gray-700 rounded-full hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div>
                <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Add Leave for</div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ $employee->name }}
                </h2>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-4 lg:px-8">
            @if ($errors->any())
                <div class="mb-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md p-3">
                    <ul class="list-disc list-inside text-sm text-red-600 dark:text-red-400">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('employees.leaves.store', $employee) }}">
                @csrf

                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 space-y-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Leave Details</h3>

                    {{-- Leave Type --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Leave Type <span class="text-red-500">*</span></label>
                        <select name="leave_type" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                            <option value="annual" {{ old('leave_type') === 'annual' ? 'selected' : '' }}>Annual Leave</option>
                            <option value="sick" {{ old('leave_type') === 'sick' ? 'selected' : '' }}>Sick Leave</option>
                            <option value="unpaid" {{ old('leave_type') === 'unpaid' ? 'selected' : '' }}>Unpaid Leave</option>
                            <option value="emergency" {{ old('leave_type') === 'emergency' ? 'selected' : '' }}>Emergency Leave</option>
                            <option value="maternity" {{ old('leave_type') === 'maternity' ? 'selected' : '' }}>Maternity Leave</option>
                            <option value="paternity" {{ old('leave_type') === 'paternity' ? 'selected' : '' }}>Paternity Leave</option>
                        </select>
                    </div>

                    {{-- Date Range --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Start Date <span class="text-red-500">*</span></label>
                            <input type="date" name="start_date" value="{{ old('start_date') }}" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">End Date <span class="text-red-500">*</span></label>
                            <input type="date" name="end_date" value="{{ old('end_date') }}" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                        </div>
                    </div>

                    {{-- Reason --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Reason</label>
                        <textarea name="reason" rows="3" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">{{ old('reason') }}</textarea>
                    </div>

                    {{-- Notes --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Internal Notes</label>
                        <textarea name="notes" rows="2" placeholder="Internal notes (not visible to employee)" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">{{ old('notes') }}</textarea>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">These notes are for HR/Admin use only</p>
                    </div>

                    {{-- Leave Balance Info --}}
                    @php $leaveBalance = $employee->leave_balance; @endphp
                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-md p-4">
                        <div class="text-sm font-medium text-blue-900 dark:text-blue-200 mb-2">Current Leave Balance</div>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-blue-700 dark:text-blue-300">Annual Leave:</span>
                                <span class="font-bold text-blue-900 dark:text-blue-100">{{ $leaveBalance['annual']['remaining'] }} / {{ $leaveBalance['annual']['total_available'] }} days</span>
                            </div>
                            <div>
                                <span class="text-blue-700 dark:text-blue-300">Sick Leave:</span>
                                <span class="font-bold text-blue-900 dark:text-blue-100">{{ $leaveBalance['sick']['remaining'] }} / {{ $leaveBalance['sick']['total'] }} days</span>
                            </div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex justify-end gap-3">
                        <a href="{{ route('employees.leaves.index', $employee) }}" class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-300 dark:hover:bg-gray-600">
                            Cancel
                        </a>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                            Add Leave Record
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
