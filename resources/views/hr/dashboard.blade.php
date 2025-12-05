<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                HR Dashboard
            </h2>
            <div class="text-sm text-gray-600 dark:text-gray-400">
                {{ now()->format('F Y') }}
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Quick Action Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6 mb-6 sm:mb-8">
                <!-- Employees Card -->
                <a href="{{ route('employees.index') }}" class="block group">
                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white transform transition hover:scale-105">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-3 bg-white/20 rounded-lg">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                            <span class="text-sm font-medium opacity-90">Manage</span>
                        </div>
                        <h3 class="text-2xl font-bold mb-1">Employees</h3>
                        <p class="text-blue-100 text-sm">View and manage employee records</p>
                    </div>
                </a>

                <!-- Payroll Card -->
                <a href="{{ route('payroll.index') }}" class="block group">
                    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white transform transition hover:scale-105">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-3 bg-white/20 rounded-lg">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <span class="text-sm font-medium opacity-90">Process</span>
                        </div>
                        <h3 class="text-2xl font-bold mb-1">Payroll</h3>
                        <p class="text-green-100 text-sm">Process and manage payroll</p>
                    </div>
                </a>

                <!-- Loans Card -->
                <a href="{{ route('loans.index') }}" class="block group">
                    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg p-6 text-white transform transition hover:scale-105">
                        <div class="flex items-center justify-between mb-4">
                            <div class="p-3 bg-white/20 rounded-lg">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 8h6m-5 0a3 3 0 110 6H9l3 3m-3-6h6m6 1a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <span class="text-sm font-medium opacity-90">Track</span>
                        </div>
                        <h3 class="text-2xl font-bold mb-1">Loans & Advances</h3>
                        <p class="text-purple-100 text-sm">Manage employee loans</p>
                    </div>
                </a>
            </div>

            <!-- Statistics Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6 sm:mb-8">
                <!-- Total Employees -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Employees</p>
                            <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $totalEmployees }}</p>
                        </div>
                        <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-lg">
                            <svg class="w-8 h-8 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4 flex gap-4 text-xs">
                        <span class="text-green-600 dark:text-green-400 font-medium">{{ $activeEmployees }} Active</span>
                        <span class="text-gray-600 dark:text-gray-400">{{ $inactiveEmployees }} Inactive</span>
                    </div>
                </div>

                <!-- Active Employees by Type -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Active Employees</p>
                            <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $activeEmployees }}</p>
                        </div>
                        <div class="p-3 bg-green-100 dark:bg-green-900 rounded-lg">
                            <svg class="w-8 h-8 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4 flex gap-4 text-xs">
                        <span class="text-blue-600 dark:text-blue-400 font-medium">{{ $fullTimeEmployees }} Full-time</span>
                        <span class="text-gray-600 dark:text-gray-400">{{ $partTimeEmployees }} Part-time</span>
                        <span class="text-gray-600 dark:text-gray-400">{{ $contractEmployees }} Contract</span>
                    </div>
                </div>

                <!-- Current Month Payroll -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">This Month Payroll</p>
                            <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ number_format($currentMonthTotalPayout, 0) }}</p>
                        </div>
                        <div class="p-3 bg-green-100 dark:bg-green-900 rounded-lg">
                            <svg class="w-8 h-8 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4 text-xs text-gray-600 dark:text-gray-400">
                        {{ $currentMonthPayrolls }} employees processed
                    </div>
                </div>

                <!-- Active Loans -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Active Loans</p>
                            <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $activeLoans }}</p>
                        </div>
                        <div class="p-3 bg-purple-100 dark:bg-purple-900 rounded-lg">
                            <svg class="w-8 h-8 text-purple-600 dark:text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 8h6m-5 0a3 3 0 110 6H9l3 3m-3-6h6m6 1a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4 text-xs">
                        <span class="text-red-600 dark:text-red-400 font-medium">{{ number_format($totalRemainingBalance, 0) }} MVR</span>
                        <span class="text-gray-600 dark:text-gray-400"> remaining</span>
                    </div>
                </div>
            </div>

            <!-- Alerts and Recent Activity -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Expiring Documents Alert -->
                @if($expiringDocuments > 0)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <div class="flex items-start gap-4">
                        <div class="p-3 bg-yellow-100 dark:bg-yellow-900 rounded-lg">
                            <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">Document Expiry Alert</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                                {{ $expiringDocuments }} employee(s) have documents expiring within the next 30 days
                            </p>
                            <a href="{{ route('employees.index', ['expiring' => 'yes']) }}" class="inline-flex items-center text-sm font-medium text-blue-600 dark:text-blue-400 hover:text-blue-700">
                                View Details
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Recent Employees -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Recent Employees</h3>
                    <div class="space-y-3">
                        @forelse($recentEmployees as $employee)
                        <a href="{{ route('employees.show', $employee) }}" class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
                                    <span class="text-sm font-semibold text-blue-600 dark:text-blue-300">{{ substr($employee->name, 0, 1) }}</span>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $employee->name }}</p>
                                    <p class="text-xs text-gray-600 dark:text-gray-400">{{ $employee->position }}</p>
                                </div>
                            </div>
                            <span class="text-xs px-2 py-1 rounded-full
                                @if($employee->status === 'active') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300
                                @elseif($employee->status === 'inactive') bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300
                                @else bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300 @endif">
                                {{ ucfirst($employee->status) }}
                            </span>
                        </a>
                        @empty
                        <p class="text-sm text-gray-600 dark:text-gray-400 text-center py-4">No employees found</p>
                        @endforelse
                    </div>
                    @if($recentEmployees->count() > 0)
                    <a href="{{ route('employees.index') }}" class="block mt-4 text-center text-sm font-medium text-blue-600 dark:text-blue-400 hover:text-blue-700">
                        View All Employees →
                    </a>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
