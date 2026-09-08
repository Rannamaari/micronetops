@if (session('last_expense'))
    @php($lastExpense = session('last_expense'))
    <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 sm:p-5 dark:border-emerald-800 dark:bg-emerald-900/20">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2 text-emerald-800 dark:text-emerald-200">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <p class="font-semibold">Expense added successfully</p>
                </div>
                <p class="mt-1 text-sm text-emerald-900 dark:text-emerald-100">
                    {{ $lastExpense['category'] }} from {{ $lastExpense['vendor'] }} —
                    MVR {{ number_format($lastExpense['amount'], 2) }} on {{ $lastExpense['date_label'] }}
                    <span class="font-semibold">({{ $lastExpense['is_paid'] ? 'Paid' : 'Due' }})</span>
                </p>
                <p class="mt-1 text-xs text-emerald-700 dark:text-emerald-300">The date, category, account, business unit, payment choice, and GST choice are kept below.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('expenses.show', $lastExpense['id']) }}" class="rounded-lg border border-emerald-300 bg-white px-4 py-2 text-sm font-medium text-emerald-800 hover:bg-emerald-100 dark:border-emerald-700 dark:bg-transparent dark:text-emerald-200">
                    View Last Expense
                </a>
                <a href="#expense-entry-form" class="rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800">
                    Add Another Expense
                </a>
                <a href="{{ route('expenses.index') }}" class="rounded-lg px-4 py-2 text-sm font-medium text-emerald-800 hover:bg-emerald-100 dark:text-emerald-200">
                    Finish
                </a>
            </div>
        </div>
    </div>
@endif
