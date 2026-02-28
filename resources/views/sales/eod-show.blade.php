<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
            <div class="flex items-center gap-3">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    End of Day — {{ $eod->business_unit === 'moto' ? 'Micro Moto' : 'Micro Cool' }}
                </h2>
                @if($eod->isDeposited())
                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">Deposited</span>
                @elseif($eod->isClosed())
                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Closed</span>
                @else
                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">Open</span>
                @endif
            </div>
            <a href="{{ route('sales.eod.index', ['date' => $eod->date->format('Y-m-d')]) }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-4 lg:px-8 space-y-6">
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

            {{-- Date & Unit Header --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ $eod->date->format('l, d F Y') }}
                </p>
            </div>

            {{-- Sales Summary --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide mb-4">Sales Summary</h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-center">
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Sales</p>
                        <p class="text-lg font-bold text-gray-900 dark:text-gray-100 mt-1">{{ $salesCount }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Cash</p>
                        <p class="text-lg font-bold text-green-600 dark:text-green-400 mt-1">{{ number_format($cashTotal, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Transfer</p>
                        <p class="text-lg font-bold text-blue-600 dark:text-blue-400 mt-1">{{ number_format($transferTotal, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Grand Total</p>
                        <p class="text-xl font-bold text-gray-900 dark:text-gray-100 mt-1">{{ number_format($grandTotal, 2) }} MVR</p>
                    </div>
                </div>
            </div>

            {{-- OPEN STATE: Cash Counting Form --}}
            @if($eod->isOpen())
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6"
                     x-data="{
                         note_500: {{ old('note_500', $eod->note_500 ?? 0) }},
                         note_100: {{ old('note_100', $eod->note_100 ?? 0) }},
                         note_50: {{ old('note_50', $eod->note_50 ?? 0) }},
                         note_20: {{ old('note_20', $eod->note_20 ?? 0) }},
                         note_10: {{ old('note_10', $eod->note_10 ?? 0) }},
                         coin_2: {{ old('coin_2', $eod->coin_2 ?? 0) }},
                         coin_1: {{ old('coin_1', $eod->coin_1 ?? 0) }},
                         get countedCash() {
                             return (this.note_500 || 0) * 500
                                  + (this.note_100 || 0) * 100
                                  + (this.note_50 || 0) * 50
                                  + (this.note_20 || 0) * 20
                                  + (this.note_10 || 0) * 10
                                  + (this.coin_2 || 0) * 2
                                  + (this.coin_1 || 0) * 1;
                         },
                         expectedCash: {{ $eod->expected_cash ?? $cashTotal }},
                         get variance() {
                             return this.countedCash - this.expectedCash;
                         }
                     }">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide mb-4">Cash Counting</h3>

                    <form method="POST" action="{{ route('sales.eod.close', $eod) }}" id="eod-close-form">
                        @csrf

                        {{-- Denomination Grid --}}
                        <div class="space-y-3">
                            <div class="grid grid-cols-3 gap-3 items-center text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide px-1">
                                <span>Denomination</span>
                                <span class="text-center">Count</span>
                                <span class="text-right">Subtotal</span>
                            </div>

                            @php
                                $denoms = [
                                    ['field' => 'note_500', 'label' => '500 MVR', 'value' => 500],
                                    ['field' => 'note_100', 'label' => '100 MVR', 'value' => 100],
                                    ['field' => 'note_50', 'label' => '50 MVR', 'value' => 50],
                                    ['field' => 'note_20', 'label' => '20 MVR', 'value' => 20],
                                    ['field' => 'note_10', 'label' => '10 MVR', 'value' => 10],
                                    ['field' => 'coin_2', 'label' => '2 MVR', 'value' => 2],
                                    ['field' => 'coin_1', 'label' => '1 MVR', 'value' => 1],
                                ];
                            @endphp

                            @foreach($denoms as $d)
                                <div class="grid grid-cols-3 gap-3 items-center bg-gray-50 dark:bg-gray-700/30 rounded-lg px-3 py-2">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $d['label'] }} &times;</span>
                                    <input type="number" name="{{ $d['field'] }}" min="0"
                                           x-model.number="{{ $d['field'] }}"
                                           class="w-full text-center rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                                           placeholder="0">
                                    <span class="text-right text-sm font-medium text-gray-900 dark:text-gray-100"
                                          x-text="(({{ $d['field'] }} || 0) * {{ $d['value'] }}).toLocaleString('en-US', {minimumFractionDigits: 2})"></span>
                                </div>
                            @endforeach

                            {{-- Counted Cash Total --}}
                            <div class="grid grid-cols-3 gap-3 items-center bg-indigo-50 dark:bg-indigo-900/20 rounded-lg px-3 py-3 border border-indigo-200 dark:border-indigo-800">
                                <span class="text-sm font-bold text-indigo-800 dark:text-indigo-200">Counted Cash</span>
                                <span></span>
                                <span class="text-right text-lg font-bold text-indigo-800 dark:text-indigo-200"
                                      x-text="countedCash.toLocaleString('en-US', {minimumFractionDigits: 2}) + ' MVR'"></span>
                            </div>
                        </div>

                        {{-- Variance Display --}}
                        <div class="mt-6 p-4 rounded-lg border"
                             :class="{
                                 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800': variance === 0,
                                 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800': variance < 0,
                                 'bg-amber-50 dark:bg-amber-900/20 border-amber-200 dark:border-amber-800': variance > 0
                             }">
                            <div class="space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">Expected Cash:</span>
                                    <span class="font-medium text-gray-900 dark:text-gray-100" x-text="expectedCash.toLocaleString('en-US', {minimumFractionDigits: 2}) + ' MVR'"></span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">Counted Cash:</span>
                                    <span class="font-medium text-gray-900 dark:text-gray-100" x-text="countedCash.toLocaleString('en-US', {minimumFractionDigits: 2}) + ' MVR'"></span>
                                </div>
                                <hr class="border-gray-200 dark:border-gray-700">
                                <div class="flex justify-between text-base font-bold">
                                    <span class="text-gray-700 dark:text-gray-300">Variance:</span>
                                    <span :class="{
                                              'text-green-700 dark:text-green-300': variance === 0,
                                              'text-red-700 dark:text-red-300': variance < 0,
                                              'text-amber-700 dark:text-amber-300': variance > 0
                                          }"
                                          x-text="(variance >= 0 ? '+' : '') + variance.toLocaleString('en-US', {minimumFractionDigits: 2}) + ' MVR'"></span>
                                </div>
                                <p class="text-xs mt-1"
                                   :class="{
                                       'text-green-600 dark:text-green-400': variance === 0,
                                       'text-red-600 dark:text-red-400': variance < 0,
                                       'text-amber-600 dark:text-amber-400': variance > 0
                                   }"
                                   x-text="variance === 0 ? 'Exact match' : (variance < 0 ? 'SHORT' : 'OVER')"></p>
                            </div>
                        </div>

                        {{-- Notes --}}
                        <div class="mt-6">
                            <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes (optional)</label>
                            <textarea name="notes" id="notes" rows="3"
                                      class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                                      placeholder="Any notes about the cash count...">{{ old('notes') }}</textarea>
                        </div>

                        {{-- Close Button --}}
                        <div class="mt-6 flex justify-end">
                            <button type="button"
                                    onclick="if(confirm('Are you sure you want to close End of Day? Sales for this date will be locked.')) document.getElementById('eod-close-form').submit();"
                                    class="inline-flex items-center gap-2 px-6 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Close End of Day
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            {{-- CLOSED / DEPOSITED STATE: Read-only Summary --}}
            @if($eod->isClosed() || $eod->isDeposited())
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide mb-4">Cash Count Summary</h3>

                    {{-- Denomination Summary --}}
                    <div class="space-y-2">
                        @php
                            $denomDisplay = [
                                ['field' => 'note_500', 'label' => '500 MVR', 'value' => 500],
                                ['field' => 'note_100', 'label' => '100 MVR', 'value' => 100],
                                ['field' => 'note_50', 'label' => '50 MVR', 'value' => 50],
                                ['field' => 'note_20', 'label' => '20 MVR', 'value' => 20],
                                ['field' => 'note_10', 'label' => '10 MVR', 'value' => 10],
                                ['field' => 'coin_2', 'label' => '2 MVR', 'value' => 2],
                                ['field' => 'coin_1', 'label' => '1 MVR', 'value' => 1],
                            ];
                        @endphp

                        @foreach($denomDisplay as $d)
                            @if($eod->{$d['field']})
                                <div class="grid grid-cols-3 gap-3 items-center bg-gray-50 dark:bg-gray-700/30 rounded-lg px-3 py-2">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $d['label'] }} &times;</span>
                                    <span class="text-center text-sm text-gray-900 dark:text-gray-100">{{ $eod->{$d['field']} }}</span>
                                    <span class="text-right text-sm font-medium text-gray-900 dark:text-gray-100">{{ number_format($eod->{$d['field']} * $d['value'], 2) }}</span>
                                </div>
                            @endif
                        @endforeach

                        {{-- Counted Cash Total --}}
                        <div class="grid grid-cols-3 gap-3 items-center bg-indigo-50 dark:bg-indigo-900/20 rounded-lg px-3 py-3 border border-indigo-200 dark:border-indigo-800">
                            <span class="text-sm font-bold text-indigo-800 dark:text-indigo-200">Counted Cash</span>
                            <span></span>
                            <span class="text-right text-lg font-bold text-indigo-800 dark:text-indigo-200">{{ number_format($eod->counted_cash, 2) }} MVR</span>
                        </div>
                    </div>

                    {{-- Variance --}}
                    @php
                        $v = (float) $eod->variance;
                        $vClass = $v == 0 ? 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800' : ($v < 0 ? 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800' : 'bg-amber-50 dark:bg-amber-900/20 border-amber-200 dark:border-amber-800');
                        $vTextClass = $v == 0 ? 'text-green-700 dark:text-green-300' : ($v < 0 ? 'text-red-700 dark:text-red-300' : 'text-amber-700 dark:text-amber-300');
                        $vLabel = $v == 0 ? 'Exact match' : ($v < 0 ? 'SHORT' : 'OVER');
                    @endphp
                    <div class="mt-4 p-4 rounded-lg border {{ $vClass }}">
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Expected Cash:</span>
                                <span class="font-medium text-gray-900 dark:text-gray-100">{{ number_format($eod->expected_cash, 2) }} MVR</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Counted Cash:</span>
                                <span class="font-medium text-gray-900 dark:text-gray-100">{{ number_format($eod->counted_cash, 2) }} MVR</span>
                            </div>
                            <hr class="border-gray-200 dark:border-gray-700">
                            <div class="flex justify-between text-base font-bold">
                                <span class="text-gray-700 dark:text-gray-300">Variance:</span>
                                <span class="{{ $vTextClass }}">{{ $v >= 0 ? '+' : '' }}{{ number_format($v, 2) }} MVR</span>
                            </div>
                            <p class="text-xs {{ $vTextClass }}">{{ $vLabel }}</p>
                        </div>
                    </div>

                    {{-- Notes --}}
                    @if($eod->notes)
                        <div class="mt-4 p-3 bg-gray-50 dark:bg-gray-700/30 rounded-lg">
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Notes</p>
                            <p class="text-sm text-gray-700 dark:text-gray-300">{{ $eod->notes }}</p>
                        </div>
                    @endif

                    {{-- Closed By --}}
                    @if($eod->closedByUser)
                        <p class="mt-4 text-xs text-gray-500 dark:text-gray-400">
                            Closed by {{ $eod->closedByUser->name }} on {{ $eod->closed_at->format('d M Y, H:i') }}
                        </p>
                    @endif

                    {{-- Deposited By --}}
                    @if($eod->isDeposited() && $eod->depositedByUser)
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            Deposited by {{ $eod->depositedByUser->name }} on {{ $eod->deposited_at->format('d M Y, H:i') }}
                        </p>
                    @endif
                </div>

                {{-- Action Buttons --}}
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6">
                    <div class="flex flex-wrap gap-3 justify-end">
                        @if($eod->isClosed() && Auth::user()->hasAnyRole(['admin', 'manager']))
                            <form method="POST" action="{{ route('sales.eod.deposit', $eod) }}"
                                  onsubmit="return confirm('Mark this cash as deposited/handed over?')">
                                @csrf
                                <div class="flex items-center gap-2 mb-3">
                                    <label class="text-xs font-medium text-gray-600 dark:text-gray-300">Deposit To</label>
                                    <select name="account_id" class="rounded border-gray-300 text-sm">
                                        @foreach ($accounts as $account)
                                            <option value="{{ $account->id }}">{{ $account->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="submit"
                                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                    Mark as Deposited
                                </button>
                            </form>
                        @endif

                        @if(!$eod->isOpen() && Auth::user()->isAdmin())
                            <form method="POST" action="{{ route('sales.eod.reopen', $eod) }}"
                                  onsubmit="return confirm('Reopen this EOD? This will unlock sales and allow re-counting.')">
                                @csrf
                                <button type="submit"
                                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                    Reopen EOD
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
