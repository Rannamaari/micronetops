<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Bill Split Results
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('rattehin.edit', $bill) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-medium">
                    Edit Bill
                </a>
                <a href="{{ route('rattehin.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg font-medium">
                    Back to Bills
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Bill Summary -->
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 overflow-hidden shadow-sm sm:rounded-lg text-white">
                <div class="p-6">
                    <h3 class="text-2xl font-bold mb-2">
                        {{ $bill->title ?: 'Bill Split' }}
                    </h3>
                    @if($bill->restaurant_name)
                        <p class="text-blue-100 mb-4">{{ $bill->restaurant_name }}</p>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-6">
                        <div class="bg-white bg-opacity-20 rounded-lg p-4">
                            <div class="text-blue-100 text-sm">Subtotal</div>
                            <div class="text-2xl font-bold">{{ $bill->currency }} {{ number_format($bill->subtotal, 2) }}</div>
                        </div>
                        @if($bill->service_charge_amount > 0)
                            <div class="bg-white bg-opacity-20 rounded-lg p-4">
                                <div class="text-blue-100 text-sm">Service ({{ $bill->service_charge_percentage }}%)</div>
                                <div class="text-2xl font-bold">{{ $bill->currency }} {{ number_format($bill->service_charge_amount, 2) }}</div>
                            </div>
                        @endif
                        @if($bill->gst_amount > 0)
                            <div class="bg-white bg-opacity-20 rounded-lg p-4">
                                <div class="text-blue-100 text-sm">GST (8%)</div>
                                <div class="text-2xl font-bold">{{ $bill->currency }} {{ number_format($bill->gst_amount, 2) }}</div>
                            </div>
                        @endif
                        <div class="bg-white bg-opacity-30 rounded-lg p-4 border-2 border-white">
                            <div class="text-blue-100 text-sm">Grand Total</div>
                            <div class="text-3xl font-bold">{{ $bill->currency }} {{ number_format($bill->grand_total, 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Share Bill Section -->
            @if(auth()->user()->hasPremiumFeature('bill_sharing'))
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path>
                            </svg>
                            Share this Bill
                        </h3>
                        <p class="text-sm text-gray-600 mb-4">Generate a professional image to share with your friends</p>

                        <div class="flex flex-wrap gap-3 mb-4">
                            <button onclick="generateAndDownloadImage()" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-lg font-medium transition-colors shadow-md">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                Generate & Download Image
                            </button>

                            <button onclick="shareImageToWhatsApp()" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                                </svg>
                                Share to WhatsApp
                            </button>
                        </div>

                        <div id="loading-indicator" class="hidden">
                            <div class="flex items-center justify-center p-4 bg-blue-50 rounded-lg">
                                <svg class="animate-spin h-5 w-5 mr-3 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span class="text-sm text-gray-700">Generating image...</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hidden Bill Image Template -->
                <div id="bill-image-template" style="position: fixed; left: -9999px; top: -9999px;">
                    <div style="width: 600px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px; font-family: 'Arial', sans-serif;">
                        <div style="background: white; border-radius: 20px; padding: 30px; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
                            <!-- Header -->
                            <div style="text-align: center; margin-bottom: 25px; border-bottom: 3px solid #667eea; padding-bottom: 20px;">
                                <h1 style="margin: 0; font-size: 32px; color: #1a202c; font-weight: bold;">{{ $bill->title ?: 'Bill Split' }}</h1>
                                @if($bill->restaurant_name)
                                    <p style="margin: 8px 0 0 0; font-size: 18px; color: #718096;">📍 {{ $bill->restaurant_name }}</p>
                                @endif
                            </div>

                            <!-- Total Amount -->
                            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 15px; padding: 20px; margin-bottom: 25px; text-align: center;">
                                <p style="margin: 0; font-size: 16px; color: #e2e8f0; font-weight: 600;">GRAND TOTAL</p>
                                <p style="margin: 10px 0 0 0; font-size: 42px; color: white; font-weight: bold;">{{ $bill->currency }} {{ number_format($bill->grand_total, 2) }}</p>
                            </div>

                            <!-- Bill Breakdown -->
                            <div style="margin-bottom: 20px;">
                                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 20px;">
                                    <div style="background: #f7fafc; padding: 12px; border-radius: 10px; text-align: center;">
                                        <p style="margin: 0; font-size: 12px; color: #718096;">Subtotal</p>
                                        <p style="margin: 5px 0 0 0; font-size: 16px; color: #2d3748; font-weight: bold;">{{ $bill->currency }} {{ number_format($bill->subtotal, 2) }}</p>
                                    </div>
                                    @if($bill->service_charge_amount > 0)
                                        <div style="background: #f7fafc; padding: 12px; border-radius: 10px; text-align: center;">
                                            <p style="margin: 0; font-size: 12px; color: #718096;">Service ({{ $bill->service_charge_percentage }}%)</p>
                                            <p style="margin: 5px 0 0 0; font-size: 16px; color: #2d3748; font-weight: bold;">{{ $bill->currency }} {{ number_format($bill->service_charge_amount, 2) }}</p>
                                        </div>
                                    @endif
                                    @if($bill->gst_amount > 0)
                                        <div style="background: #f7fafc; padding: 12px; border-radius: 10px; text-align: center;">
                                            <p style="margin: 0; font-size: 12px; color: #718096;">GST (8%)</p>
                                            <p style="margin: 5px 0 0 0; font-size: 16px; color: #2d3748; font-weight: bold;">{{ $bill->currency }} {{ number_format($bill->gst_amount, 2) }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Participants -->
                            <div style="margin-bottom: 20px;">
                                <h2 style="font-size: 20px; color: #2d3748; margin: 0 0 15px 0; font-weight: bold;">👥 Each Person Pays</h2>
                                @foreach($bill->participants as $index => $participant)
                                    <div style="background: {{ $index % 2 == 0 ? '#edf2f7' : '#e6fffa' }}; padding: 15px 20px; margin-bottom: 10px; border-radius: 12px; display: flex; justify-content: space-between; align-items: center; border-left: 4px solid {{ $index % 2 == 0 ? '#667eea' : '#38b2ac' }};">
                                        <div style="display: flex; align-items: center;">
                                            <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 18px; margin-right: 15px;">
                                                {{ strtoupper(substr($participant->name, 0, 1)) }}
                                            </div>
                                            <span style="font-size: 18px; color: #2d3748; font-weight: 600;">{{ $participant->name }}</span>
                                        </div>
                                        <span style="font-size: 24px; color: #2d3748; font-weight: bold;">{{ $bill->currency }} {{ number_format($participant->total_amount, 2) }}</span>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Footer -->
                            <div style="text-align: center; padding-top: 20px; border-top: 2px dashed #cbd5e0;">
                                <p style="margin: 0; font-size: 14px; color: #718096;">✨ Split with <strong style="color: #667eea;">Rattehin</strong></p>
                                <p style="margin: 5px 0 0 0; font-size: 12px; color: #a0aec0;">Free Bill Splitter App</p>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <!-- Premium Upgrade Prompt -->
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-lg p-6">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0">
                            <svg class="w-12 h-12 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path>
                            </svg>
                        </div>
                        <div class="flex-grow">
                            <h4 class="text-lg font-bold text-gray-900 mb-2">Share Bills with Premium</h4>
                            <p class="text-sm text-gray-700 mb-4">Upgrade to premium to share your bill splits directly on WhatsApp, Viber, and Facebook with your friends!</p>
                            <p class="text-3xl font-bold text-blue-600 mb-4">MVR 15<span class="text-lg text-gray-600">/month</span></p>

                            <div class="bg-white rounded-lg p-4 space-y-3">
                                <h5 class="font-semibold text-gray-900 mb-3">💳 Payment Details:</h5>

                                <div class="flex justify-between items-center bg-gray-50 p-2 rounded">
                                    <span class="text-sm text-gray-600">Account Number:</span>
                                    <div class="flex items-center gap-2">
                                        <code class="text-sm font-mono font-bold text-gray-900 select-all">7730000140010</code>
                                        <button onclick="navigator.clipboard.writeText('7730000140010')" class="text-blue-600 hover:text-blue-700" title="Copy account number">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <div class="flex justify-between items-center bg-gray-50 p-2 rounded">
                                    <span class="text-sm text-gray-600">Account Name:</span>
                                    <span class="text-sm font-semibold text-gray-900">MicroNET</span>
                                </div>

                                <div class="flex justify-between items-center bg-gray-50 p-2 rounded">
                                    <span class="text-sm text-gray-600">Bank:</span>
                                    <span class="text-sm font-semibold text-gray-900">Bank of Maldives</span>
                                </div>

                                <div class="flex justify-between items-center bg-blue-50 p-2 rounded border border-blue-200">
                                    <span class="text-sm text-gray-600">Amount:</span>
                                    <span class="text-lg font-bold text-blue-600">MVR 15</span>
                                </div>

                                <div class="border-t border-gray-200 pt-3 mt-3">
                                    <p class="text-sm text-gray-700">
                                        💬 After payment, send the slip to <a href="https://wa.me/9609996210" target="_blank" class="font-semibold text-blue-600 hover:text-blue-700 underline">+960 999-6210</a> (WhatsApp)
                                    </p>
                                </div>
                            </div>

                            <div class="mt-4 p-3 bg-blue-50 rounded border-l-4 border-blue-600">
                                <p class="text-xs text-gray-700"><strong>Premium Features:</strong> Bill Upload (OCR), Share Bills, Expense Tracking, and Advanced Reports</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Participant Breakdown -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-xl font-semibold mb-6">Each Person Pays</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($bill->participants as $participant)
                            <div class="border-2 border-gray-200 rounded-lg p-4 hover:border-blue-500 transition-colors">
                                <div class="flex items-center mb-3">
                                    <div class="w-12 h-12 rounded-full bg-blue-500 text-white flex items-center justify-center text-xl font-bold mr-3">
                                        {{ strtoupper(substr($participant->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="font-semibold text-lg">{{ $participant->name }}</div>
                                        <div class="text-2xl font-bold text-blue-600">
                                            {{ $bill->currency }} {{ number_format($participant->total_amount, 2) }}
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-1 text-sm text-gray-600 border-t pt-3">
                                    @if($participant->personal_items > 0)
                                        <div class="flex justify-between">
                                            <span>Personal items:</span>
                                            <span class="font-medium">{{ $bill->currency }} {{ number_format($participant->personal_items, 2) }}</span>
                                        </div>
                                    @endif
                                    @if($participant->shared_items > 0)
                                        <div class="flex justify-between">
                                            <span>Shared items:</span>
                                            <span class="font-medium">{{ $bill->currency }} {{ number_format($participant->shared_items, 2) }}</span>
                                        </div>
                                    @endif
                                    @if($participant->service_charge > 0)
                                        <div class="flex justify-between">
                                            <span>Service charge:</span>
                                            <span class="font-medium">{{ $bill->currency }} {{ number_format($participant->service_charge, 2) }}</span>
                                        </div>
                                    @endif
                                    @if($participant->gst > 0)
                                        <div class="flex justify-between">
                                            <span>GST:</span>
                                            <span class="font-medium">{{ $bill->currency }} {{ number_format($participant->gst, 2) }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Items Breakdown -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Personal Items -->
                @if($bill->items->count() > 0)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4">Personal Items</h3>
                            <div class="space-y-2">
                                @foreach($bill->items as $item)
                                    <div class="border border-gray-200 rounded-lg p-3">
                                        <div class="flex justify-between items-start mb-1">
                                            <span class="font-medium">{{ $item->name }}</span>
                                            <span class="text-gray-700 font-semibold">{{ $bill->currency }} {{ number_format($item->price, 2) }}</span>
                                        </div>
                                        @if($item->assigned_to && count($item->assigned_to) > 0)
                                            <div class="flex flex-wrap gap-1 mt-2">
                                                @foreach($item->assigned_to as $person)
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                        {{ $person }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="text-xs text-gray-500 mt-1">Not assigned</div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Shared Items -->
                @if($bill->sharedItems->count() > 0)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4">Shared Items (Split Equally)</h3>
                            <div class="space-y-2">
                                @foreach($bill->sharedItems as $item)
                                    <div class="border border-gray-200 rounded-lg p-3">
                                        <div class="flex justify-between items-center">
                                            <span class="font-medium">{{ $item->name }}</span>
                                            <div class="text-right">
                                                <div class="text-gray-700 font-semibold">{{ $bill->currency }} {{ number_format($item->price, 2) }}</div>
                                                <div class="text-xs text-gray-500">
                                                    {{ $bill->currency }} {{ number_format($item->price / $bill->participants->count(), 2) }} per person
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Additional Info -->
            <div class="bg-gray-50 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-gray-600">
                        <div>
                            <span class="font-semibold">Created:</span> {{ $bill->created_at->format('M d, Y h:i A') }}
                        </div>
                        <div>
                            <span class="font-semibold">Total Items:</span> {{ $bill->items->count() + $bill->sharedItems->count() }}
                        </div>
                        <div>
                            <span class="font-semibold">Participants:</span> {{ $bill->participants->count() }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-between items-center">
                <form action="{{ route('rattehin.destroy', $bill) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this bill? This action cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg font-medium">
                        Delete Bill
                    </button>
                </form>

                <button onclick="window.print()" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg font-medium inline-flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                    Print Bill
                </button>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        @media print {
            header, .bg-gray-600, .bg-red-600, .bg-indigo-600 {
                display: none !important;
            }
        }
    </style>
    @endpush

    @push('scripts')
    <!-- html2canvas library for image generation -->
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>

    <script>
        let generatedImageBlob = null;
        let generatedImageDataUrl = null;

        // Auto-trigger share if URL has share parameter
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('share') === 'true') {
                @if(auth()->user()->hasPremiumFeature('bill_sharing'))
                    // Automatically trigger share for premium users
                    setTimeout(() => {
                        shareImageToWhatsApp();
                    }, 500);
                @else
                    // Scroll to premium upgrade section for non-premium users
                    setTimeout(() => {
                        const upgradeSection = document.querySelector('.bg-gradient-to-r.from-blue-50');
                        if (upgradeSection) {
                            upgradeSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    }, 500);
                @endif
            }
        });

        async function generateBillImage() {
            const loadingIndicator = document.getElementById('loading-indicator');
            const template = document.getElementById('bill-image-template');

            // Show loading indicator
            loadingIndicator.classList.remove('hidden');

            try {
                // Temporarily position the template on screen for rendering
                template.style.position = 'fixed';
                template.style.left = '0';
                template.style.top = '0';
                template.style.zIndex = '-1';

                // Generate canvas from HTML
                const canvas = await html2canvas(template.querySelector('div'), {
                    backgroundColor: null,
                    scale: 2, // Higher quality
                    logging: false,
                    useCORS: true
                });

                // Hide template again
                template.style.position = 'fixed';
                template.style.left = '-9999px';
                template.style.top = '-9999px';

                // Convert canvas to blob
                return new Promise((resolve, reject) => {
                    canvas.toBlob((blob) => {
                        if (blob) {
                            generatedImageBlob = blob;
                            generatedImageDataUrl = canvas.toDataURL('image/png');
                            resolve({ blob, dataUrl: generatedImageDataUrl });
                        } else {
                            reject(new Error('Failed to generate image'));
                        }
                    }, 'image/png', 1.0);
                });
            } catch (error) {
                console.error('Error generating image:', error);
                alert('Failed to generate image. Please try again.');
                throw error;
            } finally {
                // Hide loading indicator
                loadingIndicator.classList.add('hidden');
            }
        }

        async function generateAndDownloadImage() {
            try {
                const { dataUrl } = await generateBillImage();

                // Create download link
                const link = document.createElement('a');
                link.download = 'rattehin-bill-{{ $bill->id }}.png';
                link.href = dataUrl;
                link.click();

                // Show success message
                alert('✅ Image downloaded! You can now share it on WhatsApp, Viber, or Facebook.');
            } catch (error) {
                console.error('Download failed:', error);
            }
        }

        async function shareImageToWhatsApp() {
            try {
                // First generate or use cached image
                if (!generatedImageBlob) {
                    await generateBillImage();
                }

                // Check if Web Share API is supported
                if (navigator.share && navigator.canShare) {
                    const file = new File([generatedImageBlob], 'rattehin-bill.png', { type: 'image/png' });

                    if (navigator.canShare({ files: [file] })) {
                        await navigator.share({
                            files: [file],
                            title: '{{ $bill->title ?: "Bill Split" }}',
                            text: 'Check out this bill split from Rattehin!'
                        });
                        return;
                    }
                }

                // Fallback: Download image and show instructions
                const link = document.createElement('a');
                link.download = 'rattehin-bill-{{ $bill->id }}.png';
                link.href = generatedImageDataUrl;
                link.click();

                // Show WhatsApp instructions
                setTimeout(() => {
                    if (confirm('Image downloaded! Would you like to open WhatsApp to share it?\n\nClick OK to open WhatsApp, then manually attach the downloaded image.')) {
                        window.open('https://wa.me/', '_blank');
                    }
                }, 500);

            } catch (error) {
                console.error('Share failed:', error);
                alert('Please download the image and share it manually.');
            }
        }
    </script>
    @endpush
</x-app-layout>
