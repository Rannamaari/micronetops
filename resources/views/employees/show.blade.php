<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
            <div class="flex items-center gap-3">
                <a href="{{ route('employees.index') }}"
                   class="inline-flex items-center justify-center w-10 h-10 bg-gray-100 dark:bg-gray-700 rounded-full hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                    <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
                <div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Employee</div>
                    <h2 class="font-bold text-xl text-gray-800 dark:text-gray-200">
                        {{ $employee->name }}
                    </h2>
                </div>
            </div>

            <div class="hidden sm:flex sm:gap-2">
                <a href="{{ route('employees.letter-of-appointment', $employee) }}"
                   target="_blank"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 rounded-lg font-semibold text-sm text-white shadow-sm transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span>Letter of Appointment</span>
                </a>
                <a href="{{ route('employees.edit', $employee) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 rounded-lg font-semibold text-sm text-white shadow-sm transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    <span>Edit Employee</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-4 sm:py-6">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-3 sm:space-y-4">

            {{-- Alerts --}}
            @if (session('success'))
                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                    <p class="text-sm text-green-800 dark:text-green-200">{{ session('success') }}</p>
                </div>
            @endif

            {{-- Mobile Edit Button --}}
            <div class="block sm:hidden">
                <a href="{{ route('employees.edit', $employee) }}"
                   class="flex items-center justify-center gap-2 w-full py-4 bg-indigo-600 hover:bg-indigo-700 rounded-xl font-semibold text-white shadow-lg transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    <span>Edit Employee</span>
                </a>
            </div>

            {{-- Basic Information Card --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl overflow-hidden">
                <div class="p-5 sm:p-4">
                    <div class="flex justify-between items-start mb-4 sm:mb-3">
                        <div class="flex items-center gap-3">
                            <div class="w-14 h-14 sm:w-12 sm:h-12 bg-indigo-100 dark:bg-indigo-900 rounded-full flex items-center justify-center">
                                <svg class="w-7 h-7 sm:w-6 sm:h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Full Name</div>
                                <div class="text-lg sm:text-base font-bold text-gray-900 dark:text-gray-100">
                                    {{ $employee->name }}
                                </div>
                            </div>
                        </div>
                        <span class="px-3 py-1.5 rounded-lg text-xs font-bold uppercase
                            {{ $employee->status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : '' }}
                            {{ $employee->status === 'inactive' ? 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200' : '' }}
                            {{ $employee->status === 'terminated' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : '' }}">
                            {{ ucfirst($employee->status) }}
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="flex items-start gap-3 p-3 sm:p-2.5 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <svg class="w-5 h-5 sm:w-4 sm:h-4 text-gray-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                            <div class="flex-1">
                                <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Employee Number</div>
                                <div class="text-base sm:text-sm font-semibold text-gray-900 dark:text-gray-100">
                                    {{ $employee->employee_number }}
                                </div>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 p-3 sm:p-2.5 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <svg class="w-5 h-5 sm:w-4 sm:h-4 text-gray-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            <div class="flex-1">
                                <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Company</div>
                                <div class="text-base sm:text-sm font-semibold text-gray-900 dark:text-gray-100">
                                    {{ $employee->company }}
                                </div>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 p-3 sm:p-2.5 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <svg class="w-5 h-5 sm:w-4 sm:h-4 text-gray-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                            <div class="flex-1">
                                <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Phone</div>
                                <a href="tel:{{ $employee->phone }}" class="text-base sm:text-sm font-semibold text-gray-900 dark:text-gray-100 hover:text-indigo-600 dark:hover:text-indigo-400">
                                    {{ $employee->phone }}
                                </a>
                            </div>
                        </div>

                        @if($employee->email)
                        <div class="flex items-start gap-3 p-3 sm:p-2.5 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <svg class="w-5 h-5 sm:w-4 sm:h-4 text-gray-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <div class="flex-1">
                                <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Email</div>
                                <a href="mailto:{{ $employee->email }}" class="text-base sm:text-sm font-semibold text-gray-900 dark:text-gray-100 hover:text-indigo-600 dark:hover:text-indigo-400">
                                    {{ $employee->email }}
                                </a>
                            </div>
                        </div>
                        @endif

                        <div class="flex items-start gap-3 p-3 sm:p-2.5 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <svg class="w-5 h-5 sm:w-4 sm:h-4 text-gray-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <div class="flex-1">
                                <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Position</div>
                                <div class="text-base sm:text-sm font-semibold text-gray-900 dark:text-gray-100">
                                    {{ $employee->position }}
                                </div>
                            </div>
                        </div>

                        @if($employee->department)
                        <div class="flex items-start gap-3 p-3 sm:p-2.5 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <svg class="w-5 h-5 sm:w-4 sm:h-4 text-gray-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            <div class="flex-1">
                                <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Department</div>
                                <div class="text-base sm:text-sm font-semibold text-gray-900 dark:text-gray-100">
                                    {{ $employee->department }}
                                </div>
                            </div>
                        </div>
                        @endif

                        <div class="flex items-start gap-3 p-3 sm:p-2.5 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <svg class="w-5 h-5 sm:w-4 sm:h-4 text-gray-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div class="flex-1">
                                <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Employment Type</div>
                                <span class="inline-flex items-center px-2.5 py-1 sm:px-2 sm:py-0.5 rounded-full text-sm font-medium
                                    {{ $employee->type === 'full-time' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : '' }}
                                    {{ $employee->type === 'part-time' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : '' }}
                                    {{ $employee->type === 'contract' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : '' }}">
                                    {{ ucfirst($employee->type) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="flex items-start gap-3 p-3 sm:p-2.5 bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800 rounded-lg">
                            <svg class="w-5 h-5 sm:w-4 sm:h-4 text-indigo-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <div class="flex-1">
                                <div class="text-xs text-indigo-700 dark:text-indigo-400 mb-1">Hire Date</div>
                                <div class="text-base sm:text-sm font-semibold text-indigo-900 dark:text-indigo-200">
                                    {{ $employee->hire_date?->format('M d, Y') ?? 'Not set' }}
                                </div>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 p-3 sm:p-2.5 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                            <svg class="w-5 h-5 sm:w-4 sm:h-4 text-green-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div class="flex-1">
                                <div class="text-xs text-green-700 dark:text-green-400 mb-1">Basic Salary</div>
                                <div class="text-base sm:text-sm font-bold text-green-900 dark:text-green-200">
                                    {{ number_format($employee->basic_salary, 2) }} MVR
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Employment Details & Leave Balance Card --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl overflow-hidden">
                <div class="p-4 sm:p-3 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center gap-2.5">
                        <div class="w-10 h-10 sm:w-9 sm:h-9 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 sm:w-5 sm:h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">
                            Employment & Leave Details
                        </h3>
                    </div>
                </div>

                <div class="p-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Years of Service --}}
                        <div class="p-4 rounded-lg bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border border-blue-200 dark:border-blue-800">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-xs text-blue-700 dark:text-blue-400 font-medium">Years of Service</div>
                                    <div class="text-lg font-bold text-blue-900 dark:text-blue-200">
                                        {{ $employee->service_duration }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Leave Balance --}}
                        <div class="p-4 rounded-lg bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 border border-green-200 dark:border-green-800">
                            <div class="flex items-center justify-between mb-3">
                                <div class="text-xs text-green-700 dark:text-green-400 font-medium">Leave Balance ({{ date('Y') }})</div>
                                <a href="{{ route('employees.leaves.index', $employee) }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline font-medium">
                                    Manage Leaves →
                                </a>
                            </div>
                            @php $leaveBalance = $employee->leave_balance; @endphp

                            {{-- Annual Leave --}}
                            <div class="mb-3 pb-3 border-b border-green-200 dark:border-green-700">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs font-semibold text-green-800 dark:text-green-200">Annual Leave</span>
                                    <span class="text-sm font-bold text-green-900 dark:text-green-100">
                                        {{ $leaveBalance['annual']['remaining'] }} days left
                                    </span>
                                </div>
                                <div class="space-y-1 text-xs text-green-700 dark:text-green-300">
                                    <div class="flex justify-between">
                                        <span>• Accrued this year:</span>
                                        <span class="font-medium">{{ $leaveBalance['annual']['accrued_this_year'] }} days</span>
                                    </div>
                                    @if($leaveBalance['annual']['forwarded_from_last_year'] > 0)
                                    <div class="flex justify-between">
                                        <span>• Forwarded from {{ date('Y') - 1 }}:</span>
                                        <span class="font-medium">{{ $leaveBalance['annual']['forwarded_from_last_year'] }} days</span>
                                    </div>
                                    @endif
                                    <div class="flex justify-between font-semibold">
                                        <span>• Total available:</span>
                                        <span>{{ $leaveBalance['annual']['total_available'] }} days</span>
                                    </div>
                                    <div class="flex justify-between text-red-600 dark:text-red-400">
                                        <span>• Used:</span>
                                        <span class="font-medium">{{ $leaveBalance['annual']['used'] }} days</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Sick Leave --}}
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs font-semibold text-green-800 dark:text-green-200">Sick Leave</span>
                                    <span class="text-sm font-bold text-green-900 dark:text-green-100">
                                        {{ $leaveBalance['sick']['remaining'] }} days left
                                    </span>
                                </div>
                                <div class="space-y-1 text-xs text-green-700 dark:text-green-300">
                                    <div class="flex justify-between">
                                        <span>• Annual allowance:</span>
                                        <span class="font-medium">{{ $leaveBalance['sick']['total'] }} days</span>
                                    </div>
                                    <div class="flex justify-between text-red-600 dark:text-red-400">
                                        <span>• Used:</span>
                                        <span class="font-medium">{{ $leaveBalance['sick']['used'] }} days</span>
                                    </div>
                                    <div class="text-xs italic text-gray-600 dark:text-gray-400 mt-2">
                                        * Sick leave does not carry forward
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Allowances --}}
                        <div class="p-4 rounded-lg bg-gradient-to-br from-purple-50 to-indigo-50 dark:from-purple-900/20 dark:to-indigo-900/20 border border-purple-200 dark:border-purple-800">
                            <div class="flex items-center justify-between mb-3">
                                <div class="text-xs text-purple-700 dark:text-purple-400 font-medium">Allowances</div>
                                <a href="{{ route('employees.allowances.index', $employee) }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline font-medium">
                                    Manage Allowances →
                                </a>
                            </div>
                            @php
                                $activeAllowances = $employee->allowances()->where('is_active', true)->get();
                                $totalMonthlyAllowances = $activeAllowances->where('frequency', 'monthly')->sum('amount');
                            @endphp

                            @if($activeAllowances->count() > 0)
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-semibold text-purple-800 dark:text-purple-200">Monthly Allowances</span>
                                        <span class="text-sm font-bold text-purple-900 dark:text-purple-100">
                                            {{ number_format($totalMonthlyAllowances, 2) }} MVR
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-purple-700 dark:text-purple-300">Active Allowances</span>
                                        <span class="text-sm font-semibold text-purple-900 dark:text-purple-100">
                                            {{ $activeAllowances->count() }}
                                        </span>
                                    </div>
                                </div>
                            @else
                                <div class="text-xs text-gray-600 dark:text-gray-400 italic">
                                    No allowances set
                                </div>
                            @endif
                        </div>

                        {{-- Loans & Advances --}}
                        <div class="p-4 rounded-lg bg-gradient-to-br from-red-50 to-orange-50 dark:from-red-900/20 dark:to-orange-900/20 border border-red-200 dark:border-red-800">
                            <div class="flex items-center justify-between mb-3">
                                <div class="text-xs text-red-700 dark:text-red-400 font-medium">Loans & Advances</div>
                                <a href="{{ route('employees.loans.index', $employee) }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline font-medium">
                                    Manage Loans →
                                </a>
                            </div>
                            @php
                                $activeLoans = $employee->activeLoans;
                                $totalOutstanding = $activeLoans->sum('remaining_balance');
                                $totalMonthly = $activeLoans->sum('monthly_deduction');
                            @endphp

                            @if($activeLoans->count() > 0)
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-semibold text-red-800 dark:text-red-200">Total Outstanding</span>
                                        <span class="text-sm font-bold text-red-900 dark:text-red-100">
                                            {{ number_format($totalOutstanding, 2) }} MVR
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-red-700 dark:text-red-300">Monthly Deduction</span>
                                        <span class="text-sm font-semibold text-red-900 dark:text-red-100">
                                            {{ number_format($totalMonthly, 2) }} MVR
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-red-700 dark:text-red-300">Active Loans</span>
                                        <span class="text-sm font-semibold text-red-900 dark:text-red-100">
                                            {{ $activeLoans->count() }}
                                        </span>
                                    </div>
                                </div>
                            @else
                                <div class="text-xs text-gray-600 dark:text-gray-400 italic">
                                    No active loans or advances
                                </div>
                            @endif
                        </div>

                        {{-- Bonuses --}}
                        <div class="p-4 rounded-lg bg-gradient-to-br from-yellow-50 to-amber-50 dark:from-yellow-900/20 dark:to-amber-900/20 border border-yellow-200 dark:border-yellow-800">
                            <div class="flex items-center justify-between mb-3">
                                <div class="text-xs text-yellow-700 dark:text-yellow-400 font-medium">Bonuses</div>
                                <a href="{{ route('employees.bonuses.index', $employee) }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline font-medium">
                                    Manage Bonuses →
                                </a>
                            </div>
                            @php
                                $activeBonuses = $employee->activeBonuses;
                                $monthlyBonuses = $activeBonuses->where('frequency', 'monthly')->sum('amount');
                                $oneTimeBonuses = $activeBonuses->where('frequency', 'one_time')->sum('amount');
                            @endphp

                            @if($activeBonuses->count() > 0)
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-semibold text-yellow-800 dark:text-yellow-200">Active Bonuses</span>
                                        <span class="text-sm font-bold text-yellow-900 dark:text-yellow-100">
                                            {{ $activeBonuses->count() }}
                                        </span>
                                    </div>
                                    @if($monthlyBonuses > 0)
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs text-yellow-700 dark:text-yellow-300">Monthly Bonuses</span>
                                            <span class="text-sm font-semibold text-yellow-900 dark:text-yellow-100">
                                                {{ number_format($monthlyBonuses, 2) }} MVR
                                            </span>
                                        </div>
                                    @endif
                                    <div class="text-xs text-gray-600 dark:text-gray-400 italic mt-2">
                                        Automatically applied to payroll
                                    </div>
                                </div>
                            @else
                                <div class="text-xs text-gray-600 dark:text-gray-400 italic">
                                    No active bonuses
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Compliance Status Card --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl overflow-hidden">
                <div class="p-4 sm:p-3 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center gap-2.5">
                        <div class="w-10 h-10 sm:w-9 sm:h-9 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 sm:w-5 sm:h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">
                            Compliance & Documents
                        </h3>
                    </div>
                </div>

                <div class="p-5">
                    @php $status = $employee->compliance_status; @endphp
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        {{-- Passport --}}
                        <div class="p-4 rounded-lg border-2 {{ str_contains($status['passport']['class'], 'red') ? 'border-red-300 dark:border-red-700 bg-red-50 dark:bg-red-900/20' : (str_contains($status['passport']['class'], 'orange') ? 'border-orange-300 dark:border-orange-700 bg-orange-50 dark:bg-orange-900/20' : (str_contains($status['passport']['class'], 'yellow') ? 'border-yellow-300 dark:border-yellow-700 bg-yellow-50 dark:bg-yellow-900/20' : 'border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30')) }}">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-2">
                                    <span class="text-2xl">🛂</span>
                                    <div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">Passport</div>
                                        <div class="text-sm font-bold {{ $status['passport']['class'] }}">
                                            {{ $status['passport']['status'] }}
                                        </div>
                                        @if($employee->passport_number)
                                            <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                                {{ $employee->passport_number }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                @if($employee->passport_expiry_date)
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $employee->passport_expiry_date->format('M d, Y') }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Work Permit --}}
                        <div class="p-4 rounded-lg border-2 {{ str_contains($status['work_permit']['class'], 'red') ? 'border-red-300 dark:border-red-700 bg-red-50 dark:bg-red-900/20' : (str_contains($status['work_permit']['class'], 'orange') ? 'border-orange-300 dark:border-orange-700 bg-orange-50 dark:bg-orange-900/20' : (str_contains($status['work_permit']['class'], 'yellow') ? 'border-yellow-300 dark:border-yellow-700 bg-yellow-50 dark:bg-yellow-900/20' : 'border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30')) }}">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-2">
                                    <span class="text-2xl">📋</span>
                                    <div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">Work Permit</div>
                                        <div class="text-sm font-bold {{ $status['work_permit']['class'] }}">
                                            {{ $status['work_permit']['status'] }}
                                        </div>
                                        @if($employee->work_permit_number)
                                            <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                                {{ $employee->work_permit_number }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                @if($employee->work_permit_fee_paid_until)
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $employee->work_permit_fee_paid_until->format('M d, Y') }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Visa --}}
                        <div class="p-4 rounded-lg border-2 {{ str_contains($status['visa']['class'], 'red') ? 'border-red-300 dark:border-red-700 bg-red-50 dark:bg-red-900/20' : (str_contains($status['visa']['class'], 'orange') ? 'border-orange-300 dark:border-orange-700 bg-orange-50 dark:bg-orange-900/20' : (str_contains($status['visa']['class'], 'yellow') ? 'border-yellow-300 dark:border-yellow-700 bg-yellow-50 dark:bg-yellow-900/20' : 'border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30')) }}">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-2">
                                    <span class="text-2xl">✈️</span>
                                    <div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">Visa</div>
                                        <div class="text-sm font-bold {{ $status['visa']['class'] }}">
                                            {{ $status['visa']['status'] }}
                                        </div>
                                        @if($employee->visa_number)
                                            <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                                {{ $employee->visa_number }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                @if($employee->visa_expiry_date)
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $employee->visa_expiry_date->format('M d, Y') }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Quota Slot --}}
                        <div class="p-4 rounded-lg border-2 {{ str_contains($status['quota_slot']['class'], 'red') ? 'border-red-300 dark:border-red-700 bg-red-50 dark:bg-red-900/20' : (str_contains($status['quota_slot']['class'], 'orange') ? 'border-orange-300 dark:border-orange-700 bg-orange-50 dark:bg-orange-900/20' : (str_contains($status['quota_slot']['class'], 'yellow') ? 'border-yellow-300 dark:border-yellow-700 bg-yellow-50 dark:bg-yellow-900/20' : 'border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30')) }}">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-2">
                                    <span class="text-2xl">🎫</span>
                                    <div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">Quota Slot</div>
                                        <div class="text-sm font-bold {{ $status['quota_slot']['class'] }}">
                                            {{ $status['quota_slot']['status'] }}
                                        </div>
                                        @if($employee->quota_slot_number)
                                            <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                                {{ $employee->quota_slot_number }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                @if($employee->quota_slot_fee_paid_until)
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $employee->quota_slot_fee_paid_until->format('M d, Y') }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Medical Checkup --}}
                        <div class="p-4 rounded-lg border-2 {{ str_contains($status['medical']['class'], 'red') ? 'border-red-300 dark:border-red-700 bg-red-50 dark:bg-red-900/20' : (str_contains($status['medical']['class'], 'orange') ? 'border-orange-300 dark:border-orange-700 bg-orange-50 dark:bg-orange-900/20' : (str_contains($status['medical']['class'], 'yellow') ? 'border-yellow-300 dark:border-yellow-700 bg-yellow-50 dark:bg-yellow-900/20' : 'border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30')) }}">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-2">
                                    <span class="text-2xl">🏥</span>
                                    <div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">Medical Checkup</div>
                                        <div class="text-sm font-bold {{ $status['medical']['class'] }}">
                                            {{ $status['medical']['status'] }}
                                        </div>
                                    </div>
                                </div>
                                @if($employee->medical_checkup_expiry_date)
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $employee->medical_checkup_expiry_date->format('M d, Y') }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Insurance --}}
                        <div class="p-4 rounded-lg border-2 {{ str_contains($status['insurance']['class'], 'red') ? 'border-red-300 dark:border-red-700 bg-red-50 dark:bg-red-900/20' : (str_contains($status['insurance']['class'], 'orange') ? 'border-orange-300 dark:border-orange-700 bg-orange-50 dark:bg-orange-900/20' : (str_contains($status['insurance']['class'], 'yellow') ? 'border-yellow-300 dark:border-yellow-700 bg-yellow-50 dark:bg-yellow-900/20' : 'border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30')) }}">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-2">
                                    <span class="text-2xl">🛡️</span>
                                    <div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">Insurance</div>
                                        <div class="text-sm font-bold {{ $status['insurance']['class'] }}">
                                            {{ $status['insurance']['status'] }}
                                        </div>
                                    </div>
                                </div>
                                @if($employee->insurance_expiry_date)
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $employee->insurance_expiry_date->format('M d, Y') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
