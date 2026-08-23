<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">New Purchase Order</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Prepare a supplier PO for any Micronet business unit.</p>
            </div>
            <a href="{{ route('sales.purchase-orders.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 text-sm font-medium rounded-lg transition dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-100">
                Back
            </a>
        </div>
    </x-slot>

    <div class="py-4 sm:py-6">
        <div class="max-w-6xl mx-auto px-4 sm:px-4 lg:px-8 space-y-4">
            @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm dark:bg-red-900/30 dark:border-red-800 dark:text-red-300">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('sales.purchase-orders.store') }}" class="space-y-6">
                @csrf
                @include('sales.purchase-orders.form')

                <div class="flex justify-end gap-3">
                    <a href="{{ route('sales.purchase-orders.index') }}" class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium transition dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-200">Cancel</a>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold transition">Save Purchase Order</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
