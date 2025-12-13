<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
            <h2 class="font-semibold text-lg sm:text-xl text-gray-800 leading-tight">
                Rattehin - Bill Splitter
            </h2>
            <a href="{{ route('rattehin.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded inline-flex items-center text-sm sm:text-base w-full sm:w-auto justify-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                New Bill
            </a>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Welcome Offer Banner --}}
            @if(session('offer_claimed'))
                <div class="mb-6 bg-white border-2 border-green-200 rounded-lg p-6 shadow-md">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center mb-3">
                                <svg class="w-8 h-8 mr-3 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <h3 class="text-2xl font-bold text-gray-900">Welcome to MicroNET!</h3>
                            </div>
                            <p class="text-lg mb-2 text-gray-800">
                                <strong>Congratulations!</strong> Your <strong class="text-green-600">10% discount</strong> on any service at Micro Moto Garage has been activated!
                            </p>
                            <p class="text-sm text-gray-700 mb-3">
                                ⏰ Redeem before: <strong>{{ session('offer_expires') }}</strong> (3 days from today)
                            </p>
                            <div class="bg-green-50 border border-green-200 rounded-lg p-3 mt-3">
                                <p class="text-sm font-semibold mb-2 text-gray-900">📸 How to redeem:</p>
                                <ol class="text-sm space-y-1 ml-4 list-decimal text-gray-700">
                                    <li>Take a screenshot of this message</li>
                                    <li>Visit MicroNET Micro Moto Garage</li>
                                    <li>Show this screenshot to get 10% off any service!</li>
                                </ol>
                            </div>
                        </div>
                        <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            @endif

            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-3">Rattehin - Bill Sharing Made Easy</h3>
                        <p class="text-gray-600 mb-3">Split restaurant bills easily with friends and family</p>
                        <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
                            <p class="text-sm text-gray-700">
                                <strong>About this app:</strong> Rattehin started as a school project and is now helping friends and colleagues split bills fairly.
                                Simply add diners, assign items to people, and let the app calculate who owes what—including service charges and GST!
                            </p>
                        </div>
                    </div>

                    @if($bills->count() > 0)
                        <!-- Mobile Card View -->
                        <div class="space-y-4 sm:hidden">
                            @foreach($bills as $bill)
                                <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden cursor-pointer hover:shadow-md transition-shadow"
                                     onclick="window.location='{{ route('rattehin.show', $bill) }}'">

                                    <!-- Card Header -->
                                    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 p-4 text-white">
                                        <h3 class="font-semibold text-lg">{{ $bill->title ?? 'Untitled Bill' }}</h3>
                                        @if($bill->restaurant_name)
                                            <p class="text-sm text-blue-100 flex items-center mt-1">
                                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                                                </svg>
                                                {{ $bill->restaurant_name }}
                                            </p>
                                        @endif
                                    </div>

                                    <!-- Card Body -->
                                    <div class="p-4">
                                        <!-- Total Amount - Prominent -->
                                        <div class="mb-3 pb-3 border-b border-gray-200">
                                            <p class="text-xs text-gray-500 uppercase">Grand Total</p>
                                            <p class="text-2xl font-bold text-gray-900">{{ $bill->currency }} {{ number_format($bill->grand_total, 2) }}</p>
                                            <p class="text-xs text-gray-500 mt-1">{{ $bill->items->count() + $bill->sharedItems->count() }} items</p>
                                        </div>

                                        <!-- Participants -->
                                        <div class="mb-3">
                                            <p class="text-xs text-gray-500 mb-2">Participants ({{ $bill->participants->count() }})</p>
                                            <div class="flex -space-x-2">
                                                @foreach($bill->participants->take(5) as $participant)
                                                    <div class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-blue-500 text-white text-sm font-semibold border-2 border-white" title="{{ $participant->name }}">
                                                        {{ strtoupper(substr($participant->name, 0, 1)) }}
                                                    </div>
                                                @endforeach
                                                @if($bill->participants->count() > 5)
                                                    <div class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-gray-300 text-gray-700 text-sm font-semibold border-2 border-white">
                                                        +{{ $bill->participants->count() - 5 }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Date and Actions -->
                                        <div class="flex items-center justify-between pt-3 border-t border-gray-200">
                                            <div class="text-xs text-gray-500">
                                                {{ $bill->created_at->format('M d, Y') }}
                                                <span class="block text-gray-400">{{ $bill->created_at->diffForHumans() }}</span>
                                            </div>
                                            <div class="flex gap-2" onclick="event.stopPropagation()">
                                                @if(auth()->user()->hasPremiumFeature('bill_sharing'))
                                                    <button onclick="shareBillMobile({{ $bill->id }})" class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition-colors" title="Share">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path>
                                                        </svg>
                                                    </button>
                                                @endif
                                                <a href="{{ route('rattehin.edit', $bill) }}" class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors" title="Edit">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                    </svg>
                                                </a>
                                                <form action="{{ route('rattehin.destroy', $bill) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this bill?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Delete">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Desktop Table View -->
                        <div class="hidden sm:block overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Title / Restaurant
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Participants
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Total Amount
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Date
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($bills as $bill)
                                        <tr class="hover:bg-gray-50 cursor-pointer transition-colors group"
                                            onclick="window.location='{{ route('rattehin.show', $bill) }}'">
                                            <td class="px-6 py-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $bill->title ?? 'Untitled Bill' }}
                                                </div>
                                                @if($bill->restaurant_name)
                                                    <div class="text-sm text-gray-500">
                                                        📍 {{ $bill->restaurant_name }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="flex -space-x-2">
                                                    @foreach($bill->participants->take(3) as $participant)
                                                        <div class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-500 text-white text-xs font-semibold border-2 border-white" title="{{ $participant->name }}">
                                                            {{ strtoupper(substr($participant->name, 0, 1)) }}
                                                        </div>
                                                    @endforeach
                                                    @if($bill->participants->count() > 3)
                                                        <div class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-300 text-gray-700 text-xs font-semibold border-2 border-white">
                                                            +{{ $bill->participants->count() - 3 }}
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="text-xs text-gray-500 mt-1">
                                                    {{ $bill->participants->count() }} {{ Str::plural('person', $bill->participants->count()) }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-sm font-semibold text-gray-900">
                                                    {{ $bill->currency }} {{ number_format($bill->grand_total, 2) }}
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    {{ $bill->items->count() + $bill->sharedItems->count() }} items
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-500">
                                                {{ $bill->created_at->format('M d, Y') }}
                                                <div class="text-xs text-gray-400">
                                                    {{ $bill->created_at->diffForHumans() }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 text-right text-sm font-medium" onclick="event.stopPropagation()">
                                                @if(auth()->user()->hasPremiumFeature('bill_sharing'))
                                                    <button onclick="shareBillDesktop({{ $bill->id }})" class="text-green-600 hover:text-green-900 mr-3" title="Share">
                                                        <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path>
                                                        </svg>
                                                    </button>
                                                @endif
                                                <a href="{{ route('rattehin.show', $bill) }}" class="text-blue-600 hover:text-blue-900 mr-3">
                                                    View
                                                </a>
                                                <a href="{{ route('rattehin.edit', $bill) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                                    Edit
                                                </a>
                                                <form action="{{ route('rattehin.destroy', $bill) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this bill?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900">
                                                        Delete
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-6">
                            {{ $bills->links() }}
                        </div>
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No bills yet</h3>
                            <p class="mt-1 text-sm text-gray-500">Get started by creating your first bill split.</p>
                            <div class="mt-6">
                                <a href="{{ route('rattehin.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    Create New Bill
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Subtle tagline for best customers -->
            <div class="mt-8 text-center">
                <p class="text-xs text-gray-400 italic">Split bills. Stay friends.</p>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function shareBillMobile(billId) {
            // Navigate to the bill page with a share trigger
            window.location.href = `/rattehin/${billId}?share=true`;
        }

        function shareBillDesktop(billId) {
            // Navigate to the bill page with a share trigger
            window.location.href = `/rattehin/${billId}?share=true`;
        }
    </script>
    @endpush
</x-app-layout>
