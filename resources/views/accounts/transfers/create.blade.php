<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('New Transfer') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('accounts.transfers.store') }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium">From Account</label>
                            <select name="from_account_id" class="mt-1 w-full rounded border-gray-300" required>
                                @foreach ($accounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium">To Account</label>
                            <select name="to_account_id" class="mt-1 w-full rounded border-gray-300" required>
                                @foreach ($accounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium">Amount</label>
                                <input type="number" step="0.01" name="amount" class="mt-1 w-full rounded border-gray-300" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium">Date</label>
                                <input type="date" name="occurred_at" value="{{ now()->toDateString() }}" class="mt-1 w-full rounded border-gray-300" required>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Notes</label>
                            <textarea name="notes" rows="2" class="mt-1 w-full rounded border-gray-300"></textarea>
                        </div>
                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('accounts.transfers.index') }}" class="text-gray-600 hover:underline">Cancel</a>
                            <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
