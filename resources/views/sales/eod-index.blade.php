<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                End of Day
            </h2>
            <a href="{{ route('sales.daily.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Sales
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-4 lg:px-8 space-y-6">
            {{-- Flash messages --}}
            @if(session('success'))
                <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-300 px-4 py-3 rounded-lg text-sm">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 px-4 py-3 rounded-lg text-sm">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Date Picker --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6">
                <form method="GET" action="{{ route('sales.eod.index') }}" class="flex items-end gap-3">
                    <div>
                        <label for="date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date</label>
                        <input type="date" name="date" id="date" value="{{ $date }}"
                               class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <button type="submit"
                            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                        View
                    </button>
                </form>
            </div>

            {{-- Unit Cards --}}
            @php
                $unitLabels = ['moto' => 'Micro Moto', 'cool' => 'Micro Cool'];
                $unitColors = [
                    'moto' => ['bg' => 'bg-orange-50 dark:bg-orange-900/20', 'border' => 'border-orange-200 dark:border-orange-800', 'badge' => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200'],
                    'cool' => ['bg' => 'bg-cyan-50 dark:bg-cyan-900/20', 'border' => 'border-cyan-200 dark:border-cyan-800', 'badge' => 'bg-cyan-100 text-cyan-800 dark:bg-cyan-900 dark:text-cyan-200'],
                ];
            @endphp

            @foreach(['moto', 'cool'] as $unit)
                @php
                    $data = $unitData[$unit];
                    $eod = $data['eod'];
                @endphp
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="p-4 sm:p-6 border-b border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium {{ $unitColors[$unit]['badge'] }}">
                                {{ $unitLabels[$unit] }}
                            </span>
                            <h3 class="text-base font-semibold text-gray-800 dark:text-gray-200">
                                {{ \Carbon\Carbon::parse($date)->format('D, d M Y') }}
                            </h3>
                        </div>

                        {{-- Status Badge --}}
                        @if($eod)
                            @if($eod->isDeposited())
                                <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">Deposited</span>
                            @elseif($eod->isClosed())
                                <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Closed</span>
                            @else
                                <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">Open</span>
                            @endif
                        @else
                            <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400">Not Started</span>
                        @endif
                    </div>

                    <div class="p-4 sm:p-6 space-y-4">
                        {{-- Sales Summary --}}
                        <div class="grid grid-cols-3 gap-4 text-center">
                            <div>
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Submitted Sales</p>
                                <p class="text-lg font-bold text-gray-900 dark:text-gray-100 mt-1">{{ $data['submitted_count'] }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Cash</p>
                                <p class="text-lg font-bold text-green-600 dark:text-green-400 mt-1">{{ number_format($data['cash_total'], 2) }} MVR</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Transfer</p>
                                <p class="text-lg font-bold text-blue-600 dark:text-blue-400 mt-1">{{ number_format($data['transfer_total'], 2) }} MVR</p>
                            </div>
                        </div>

                        {{-- Draft Warning --}}
                        @if($data['draft_count'] > 0)
                            <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 text-amber-700 dark:text-amber-300 px-4 py-3 rounded-lg text-sm flex items-center gap-2">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                                {{ $data['draft_count'] }} draft sale(s) remaining. Submit or delete all drafts before starting EOD.
                            </div>
                        @endif

                        {{-- Action Button --}}
                        <div class="flex justify-end">
                            @if($eod)
                                <a href="{{ route('sales.eod.show', $eod) }}"
                                   class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg transition shadow-sm
                                       {{ $eod->isOpen() ? 'bg-yellow-600 hover:bg-yellow-700 text-white' : 'bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300' }}">
                                    {{ $eod->isOpen() ? 'Continue' : 'View' }}
                                </a>
                            @else
                                <form method="POST" action="{{ route('sales.eod.create') }}">
                                    @csrf
                                    <input type="hidden" name="date" value="{{ $date }}">
                                    <input type="hidden" name="business_unit" value="{{ $unit }}">
                                    <button type="submit"
                                            {{ $data['draft_count'] > 0 ? 'disabled' : '' }}
                                            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg transition shadow-sm
                                                {{ $data['draft_count'] > 0 ? 'bg-gray-300 text-gray-500 cursor-not-allowed' : 'bg-indigo-600 hover:bg-indigo-700 text-white' }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                        Start EOD
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>
