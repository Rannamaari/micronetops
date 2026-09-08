@props([
    'accounts',
    'selected' => null,
    'isPaid' => true,
    'dueDate' => null,
])

@php
    $paid = (bool) old('is_paid', $isPaid);
    $dueDateValue = old('due_date', $dueDate instanceof \Carbon\CarbonInterface ? $dueDate->toDateString() : $dueDate);
@endphp

<div class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/40">
    <input type="hidden" name="is_paid" value="0">
    <label class="inline-flex cursor-pointer items-center gap-3">
        <input type="checkbox" id="expense-is-paid" name="is_paid" value="1"
               class="h-5 w-5 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
               @checked($paid)>
        <span>
            <span class="block text-sm font-semibold text-gray-900 dark:text-gray-100">Paid</span>
            <span class="block text-xs text-gray-500 dark:text-gray-400">Untick this when the supplier gave the expense on credit.</span>
        </span>
    </label>

    <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
        <div>
            <label for="expense-account" class="block text-sm font-medium">
                <span id="expense-account-label">{{ $paid ? 'Paid From Account' : 'Planned Payment Account' }}</span>
                <span id="expense-account-required" class="text-red-500 {{ $paid ? '' : 'hidden' }}">*</span>
            </label>
            <select id="expense-account" name="account_id" class="mt-1 w-full rounded border-gray-300" @required($paid)>
                <x-expense-account-options :accounts="$accounts" :selected="$selected" />
            </select>
            <p class="mt-1 text-xs text-gray-500">The account balance is deducted only when this expense is marked paid.</p>
            @error('account_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div id="expense-due-date-wrap" class="{{ $paid ? 'hidden' : '' }}">
            <label for="expense-due-date" class="block text-sm font-medium">Due Date</label>
            <input type="date" id="expense-due-date" name="due_date" value="{{ $dueDateValue }}"
                   class="mt-1 w-full rounded border-gray-300">
            <p class="mt-1 text-xs text-gray-500">Optional, but recommended for credit purchases.</p>
            @error('due_date')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>
</div>

@once
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const paid = document.getElementById('expense-is-paid');
            const account = document.getElementById('expense-account');
            const accountLabel = document.getElementById('expense-account-label');
            const required = document.getElementById('expense-account-required');
            const dueDateWrap = document.getElementById('expense-due-date-wrap');
            if (!paid || !account || !dueDateWrap) return;

            const updatePaymentFields = () => {
                account.required = paid.checked;
                accountLabel.textContent = paid.checked ? 'Paid From Account' : 'Planned Payment Account';
                required?.classList.toggle('hidden', !paid.checked);
                dueDateWrap.classList.toggle('hidden', paid.checked);
            };

            paid.addEventListener('change', updatePaymentFields);
            updatePaymentFields();
        });
    </script>
@endonce
