<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Welcome Offer Banner --}}
            @if(session('offer_claimed'))
                <div class="mb-6 bg-gradient-to-r from-green-500 to-emerald-600 rounded-lg p-6 text-white shadow-xl">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center mb-3">
                                <svg class="w-8 h-8 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <h3 class="text-2xl font-bold">Welcome to MicroNET!</h3>
                            </div>
                            <p class="text-lg mb-2">
                                <strong>Congratulations!</strong> Your FREE OIL CHANGE (worth MVR 300) has been activated!
                            </p>
                            <p class="text-sm opacity-90 mb-3">
                                ⏰ Valid until: <strong>{{ session('offer_expires') }}</strong> (3 days from today)
                            </p>
                            <div class="bg-white bg-opacity-20 rounded-lg p-3 mt-3">
                                <p class="text-sm font-semibold mb-1">📸 How to claim:</p>
                                <ol class="text-sm space-y-1 ml-4 list-decimal">
                                    <li>Take a screenshot of this message</li>
                                    <li>Visit MicroNET Micro Moto Garage</li>
                                    <li>Show this screenshot to claim your free oil change!</li>
                                </ol>
                            </div>
                        </div>
                        <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            @endif

            {{-- Key Metrics Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                {{-- Total Customers --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Customers</p>
                                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ number_format($totalCustomers) }}</p>
                            </div>
                            <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-full">
                                <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Jobs This Week --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Jobs This Week</p>
                                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ number_format($jobsThisWeek) }}</p>
                            </div>
                            <div class="p-3 bg-green-100 dark:bg-green-900 rounded-full">
                                <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Jobs This Month --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Jobs This Month</p>
                                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ number_format($jobsThisMonth) }}</p>
                            </div>
                            <div class="p-3 bg-purple-100 dark:bg-purple-900 rounded-full">
                                <svg class="w-8 h-8 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Sales Today --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Sales Today</p>
                                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">MVR {{ number_format($salesToday, 2) }}</p>
                            </div>
                            <div class="p-3 bg-yellow-100 dark:bg-yellow-900 rounded-full">
                                <svg class="w-8 h-8 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Second Row of Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                {{-- Sales This Month --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Sales This Month</p>
                                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">MVR {{ number_format($salesThisMonth, 2) }}</p>
                            </div>
                            <div class="p-3 bg-indigo-100 dark:bg-indigo-900 rounded-full">
                                <svg class="w-8 h-8 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Total Inventory Items --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Inventory Items</p>
                                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ number_format($totalInventoryItems) }}</p>
                            </div>
                            <div class="p-3 bg-teal-100 dark:bg-teal-900 rounded-full">
                                <svg class="w-8 h-8 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Low Stock Items --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Low Stock Items</p>
                                <p class="text-3xl font-bold {{ $lowStockItems > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white' }} mt-2">{{ number_format($lowStockItems) }}</p>
                            </div>
                            <div class="p-3 {{ $lowStockItems > 0 ? 'bg-red-100 dark:bg-red-900' : 'bg-gray-100 dark:bg-gray-700' }} rounded-full">
                                <svg class="w-8 h-8 {{ $lowStockItems > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-600 dark:text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                            </div>
                        </div>
                        @if($lowStockItems > 0)
                            <a href="{{ route('inventory.index', ['filter' => 'low_stock']) }}" class="mt-2 text-sm text-red-600 dark:text-red-400 hover:underline inline-block">
                                View low stock items →
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Petty Cash Balance --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Petty Cash Balance</p>
                                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">MVR {{ number_format($pettyCashBalance, 2) }}</p>
                            </div>
                            <div class="p-3 bg-emerald-100 dark:bg-emerald-900 rounded-full">
                                <svg class="w-8 h-8 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <a href="{{ route('petty-cash.index') }}" class="mt-2 text-sm text-gray-600 dark:text-gray-400 hover:underline inline-block">
                            View petty cash →
                        </a>
                    </div>
                </div>
            </div>

            {{-- Overdue Leads Alert --}}
            @if($overdueLeads->count() > 0)
                <div class="mb-6 bg-red-50 dark:bg-red-900/20 border-2 border-red-200 dark:border-red-800 rounded-lg overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-red-100 dark:bg-red-900 rounded-full">
                                    <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-red-900 dark:text-red-100">Overdue Follow-ups</h3>
                                    <p class="text-sm text-red-700 dark:text-red-300">{{ $overdueLeads->count() }} lead{{ $overdueLeads->count() > 1 ? 's' : '' }} need{{ $overdueLeads->count() == 1 ? 's' : '' }} immediate attention</p>
                                </div>
                            </div>
                            <a href="{{ route('leads.index', ['status' => 'all']) }}" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium transition">
                                View All Leads
                            </a>
                        </div>

                        <div class="space-y-3">
                            @foreach($overdueLeads as $lead)
                                <a href="{{ route('leads.show', $lead) }}" class="block bg-white dark:bg-gray-800 rounded-lg p-4 hover:shadow-md transition border-l-4 border-red-500">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2 mb-1">
                                                <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $lead->name }}</span>
                                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                                                    {{ $lead->priority === 'high' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' :
                                                       ($lead->priority === 'medium' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' :
                                                       'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200') }}">
                                                    {{ ucfirst($lead->priority) }}
                                                </span>
                                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                    {{ ucfirst($lead->status) }}
                                                </span>
                                            </div>
                                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                                {{ $lead->phone }} • Interested in: {{ ucfirst($lead->interested_in) }}
                                            </div>
                                            <div class="flex items-center gap-1 mt-2 text-xs text-red-600 dark:text-red-400 font-semibold">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                                </svg>
                                                Overdue since {{ $lead->follow_up_date->format('M d, Y') }} ({{ $lead->follow_up_date->diffForHumans() }})
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            @if($lead->call_attempts > 0)
                                                <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">
                                                    {{ $lead->call_attempts }} call{{ $lead->call_attempts > 1 ? 's' : '' }}
                                                </div>
                                            @endif
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                            </svg>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            {{-- Sales Breakdown Row --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                {{-- AC Sales This Month --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">AC Sales This Month</p>
                                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">MVR {{ number_format($salesThisMonthAC, 2) }}</p>
                                @if($salesThisMonth > 0)
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        {{ number_format(($salesThisMonthAC / $salesThisMonth) * 100, 1) }}% of total
                                    </p>
                                @endif
                            </div>
                            <div class="p-3 bg-cyan-100 dark:bg-cyan-900 rounded-full">
                                <svg class="w-8 h-8 text-cyan-600 dark:text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Moto Sales This Month --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Micro Moto Sales This Month</p>
                                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">MVR {{ number_format($salesThisMonthMoto, 2) }}</p>
                                @if($salesThisMonth > 0)
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        {{ number_format(($salesThisMonthMoto / $salesThisMonth) * 100, 1) }}% of total
                                    </p>
                                @endif
                            </div>
                            <div class="p-3 bg-orange-100 dark:bg-orange-900 rounded-full">
                                <svg class="w-8 h-8 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Charts Section --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                {{-- Daily Sales Chart --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Daily Total Sales (Last 10 Days)</h3>
                    <canvas id="dailySalesChart"></canvas>
                </div>

                {{-- Monthly Trends Chart --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Monthly Revenue Trends</h3>
                    <canvas id="monthlyTrendsChart"></canvas>
                </div>
            </div>

            {{-- Best Selling Items & Services --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Micro Cool (AC) Best Sellers --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Micro Cool - Best Sellers (Last 30 Days)</h3>

                    {{-- Best Services --}}
                    <div class="mb-4">
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Top Services</h4>
                        @forelse($bestSellingData['acServices'] as $item)
                            <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-700">
                                <span class="text-sm text-gray-900 dark:text-gray-100">{{ $item->inventoryItem->name ?? 'Unknown' }}</span>
                                <span class="text-sm font-semibold text-green-600 dark:text-green-400">{{ $item->total_quantity }} times</span>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500 dark:text-gray-400">No data yet</p>
                        @endforelse
                    </div>

                    {{-- Best Items --}}
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Top Items</h4>
                        @forelse($bestSellingData['acItems'] as $item)
                            <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-700">
                                <span class="text-sm text-gray-900 dark:text-gray-100">{{ $item->inventoryItem->name ?? 'Unknown' }}</span>
                                <span class="text-sm font-semibold text-blue-600 dark:text-blue-400">{{ $item->total_quantity }} units</span>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500 dark:text-gray-400">No data yet</p>
                        @endforelse
                    </div>
                </div>

                {{-- Micro Moto Garage Best Sellers --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Micro Moto - Best Sellers (Last 30 Days)</h3>

                    {{-- Best Services --}}
                    <div class="mb-4">
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Top Services</h4>
                        @forelse($bestSellingData['motoServices'] as $item)
                            <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-700">
                                <span class="text-sm text-gray-900 dark:text-gray-100">{{ $item->inventoryItem->name ?? 'Unknown' }}</span>
                                <span class="text-sm font-semibold text-green-600 dark:text-green-400">{{ $item->total_quantity }} times</span>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500 dark:text-gray-400">No data yet</p>
                        @endforelse
                    </div>

                    {{-- Best Items --}}
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Top Items</h4>
                        @forelse($bestSellingData['motoItems'] as $item)
                            <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-700">
                                <span class="text-sm text-gray-900 dark:text-gray-100">{{ $item->inventoryItem->name ?? 'Unknown' }}</span>
                                <span class="text-sm font-semibold text-blue-600 dark:text-blue-400">{{ $item->total_quantity }} units</span>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500 dark:text-gray-400">No data yet</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Chart.js Library --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        // Daily Sales Chart
        const dailySalesCtx = document.getElementById('dailySalesChart').getContext('2d');
        new Chart(dailySalesCtx, {
            type: 'line',
            data: {
                labels: @json($dailySalesData['labels']),
                datasets: [{
                    label: 'Daily Sales (MVR)',
                    data: @json($dailySalesData['data']),
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: true,
                        labels: {
                            color: document.documentElement.classList.contains('dark') ? '#9CA3AF' : '#374151'
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: document.documentElement.classList.contains('dark') ? '#9CA3AF' : '#374151'
                        },
                        grid: {
                            color: document.documentElement.classList.contains('dark') ? '#374151' : '#E5E7EB'
                        }
                    },
                    x: {
                        ticks: {
                            color: document.documentElement.classList.contains('dark') ? '#9CA3AF' : '#374151'
                        },
                        grid: {
                            color: document.documentElement.classList.contains('dark') ? '#374151' : '#E5E7EB'
                        }
                    }
                }
            }
        });

        // Monthly Trends Chart
        const monthlyTrendsCtx = document.getElementById('monthlyTrendsChart').getContext('2d');
        new Chart(monthlyTrendsCtx, {
            type: 'bar',
            data: {
                labels: @json($monthlyTrendsData['labels']),
                datasets: [
                    {
                        label: 'Micro Cool (AC)',
                        data: @json($monthlyTrendsData['acData']),
                        backgroundColor: 'rgba(16, 185, 129, 0.8)',
                        borderColor: 'rgb(16, 185, 129)',
                        borderWidth: 1
                    },
                    {
                        label: 'Micro Moto Garage',
                        data: @json($monthlyTrendsData['motoData']),
                        backgroundColor: 'rgba(249, 115, 22, 0.8)',
                        borderColor: 'rgb(249, 115, 22)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: true,
                        labels: {
                            color: document.documentElement.classList.contains('dark') ? '#9CA3AF' : '#374151'
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: document.documentElement.classList.contains('dark') ? '#9CA3AF' : '#374151'
                        },
                        grid: {
                            color: document.documentElement.classList.contains('dark') ? '#374151' : '#E5E7EB'
                        }
                    },
                    x: {
                        ticks: {
                            color: document.documentElement.classList.contains('dark') ? '#9CA3AF' : '#374151'
                        },
                        grid: {
                            color: document.documentElement.classList.contains('dark') ? '#374151' : '#E5E7EB'
                        }
                    }
                }
            }
        });
    </script>
</x-app-layout>
