<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Create New Bill Split
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <div x-data="billSplitter()" x-init="init()" class="space-y-6">

                <!-- Bill Information -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Bill Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Bill Title (Optional)</label>
                                <input type="text" x-model="billData.title" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., Dinner with friends">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Restaurant Name (Optional)</label>
                                <input type="text" x-model="billData.restaurant_name" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., The Sea House">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bill Scanning -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            Scan Bill (Optional)
                            @if(auth()->user()->hasPremiumFeature('bill_upload'))
                                <span class="ml-2 px-2 py-1 bg-gradient-to-r from-yellow-400 to-orange-500 text-white text-xs font-bold rounded-full">PREMIUM</span>
                            @endif
                        </h3>

                        @if(!auth()->user()->hasPremiumFeature('bill_upload'))
                            <!-- Upgrade Prompt -->
                            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-lg p-6">
                                <div class="text-center mb-4">
                                    <svg class="mx-auto h-12 w-12 text-blue-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                    </svg>
                                    <h4 class="text-lg font-bold text-gray-900 mb-2">Unlock Bill Scanning with Premium</h4>
                                    <p class="text-gray-700 mb-4">Upload photos of your bills and let OCR extract items automatically. Save time and reduce errors!</p>
                                    <div class="bg-white rounded-lg p-4 mb-4 inline-block">
                                        <p class="text-sm text-gray-600 mb-1">Premium Features</p>
                                        <p class="text-3xl font-bold text-blue-600">MVR 15<span class="text-lg text-gray-600">/month</span></p>
                                    </div>
                                </div>

                                <div class="bg-white rounded-lg p-4 text-left space-y-3">
                                    <h5 class="font-semibold text-gray-900 mb-3">💳 Payment Details:</h5>

                                    <div class="space-y-2">
                                        <div class="flex justify-between items-center bg-gray-50 p-2 rounded">
                                            <span class="text-sm text-gray-600">Account Number:</span>
                                            <div class="flex items-center gap-2">
                                                <code class="text-sm font-mono font-bold text-gray-900 select-all">7730000140010</code>
                                                <button onclick="navigator.clipboard.writeText('7730000140010')" class="text-blue-600 hover:text-blue-700" title="Copy">
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

                                        <div class="flex justify-between items-center bg-green-50 p-2 rounded border border-green-200">
                                            <span class="text-sm text-gray-600">Amount:</span>
                                            <span class="text-sm font-bold text-green-700">MVR 15</span>
                                        </div>
                                    </div>

                                    <div class="border-t border-gray-200 pt-3 mt-3">
                                        <p class="text-xs text-gray-600 mb-2">📱 <strong>After payment:</strong></p>
                                        <p class="text-sm text-gray-700">Send payment slip to <a href="https://wa.me/9609996210" target="_blank" class="font-semibold text-blue-600 hover:text-blue-700 underline">+960 999-6210</a> (WhatsApp)</p>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="space-y-4">
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                                <input type="file" id="billImage" @change="handleImageUpload" accept="image/*" capture="environment" class="hidden">
                                <label for="billImage" class="cursor-pointer">
                                    <div class="space-y-2">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                        </svg>
                                        <div class="text-sm text-gray-600">
                                            <span class="font-semibold text-blue-600 hover:text-blue-700">Click to upload</span> or take a photo
                                        </div>
                                        <p class="text-xs text-gray-500">PNG, JPG • Auto-compressed for faster upload</p>
                                    </div>
                                </label>
                            </div>

                            <div x-show="scanning" class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <div class="flex items-center">
                                    <svg class="animate-spin h-5 w-5 text-blue-600 mr-3" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span class="text-blue-700">Scanning bill...</span>
                                </div>
                            </div>

                            <div x-show="scannedItems.length > 0" class="space-y-2">
                                <h4 class="font-semibold text-sm text-gray-700">Scanned Items (<span x-text="scannedItems.length"></span>):</h4>
                                <template x-for="item in scannedItems" :key="item.id">
                                    <div class="flex items-center justify-between bg-green-50 border border-green-200 rounded-lg p-3">
                                        <div class="flex-1">
                                            <span class="text-sm font-medium" x-text="item.name"></span>
                                            <span class="text-xs text-gray-600 ml-2" x-show="item.quantity > 1" x-text="'(Qty: ' + item.quantity + ')'"></span>
                                        </div>
                                        <span class="text-sm font-semibold text-green-700" x-text="'MVR ' + item.price.toFixed(2)"></span>
                                    </div>
                                </template>
                                <button @click="addAllScannedItems" class="w-full bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-lg text-sm font-medium">
                                    Add All Items to Personal Items
                                </button>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Participants -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Participants</h3>
                        <div class="flex gap-2 mb-4">
                            <input type="text" x-model="newParticipantName" @keyup.enter="addParticipant" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Enter person's name">
                            <button @click="addParticipant" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium">
                                Add
                            </button>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <template x-for="(person, index) in participants" :key="index">
                                <div class="inline-flex items-center bg-blue-100 text-blue-800 px-3 py-1 rounded-full">
                                    <span x-text="person"></span>
                                    <button @click="removeParticipant(index)" class="ml-2 text-blue-600 hover:text-blue-800">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                            </template>
                        </div>

                        <div x-show="participants.length === 0" class="text-gray-500 text-sm mt-2">
                            Add at least one person to split the bill
                        </div>
                    </div>
                </div>

                <!-- Personal Items -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Personal Items</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <input type="text" x-model="newItem.name" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Item name">
                            <input type="number" x-model="newItem.price" step="0.01" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Price">
                            <button @click="addItem" :disabled="participants.length === 0" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-medium disabled:bg-gray-400">
                                Add Item
                            </button>
                        </div>

                        <div class="space-y-3">
                            <template x-for="(item, index) in items" :key="index">
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <div class="flex justify-between items-start mb-2">
                                        <div>
                                            <span class="font-medium" x-text="item.name"></span>
                                            <span class="text-gray-600 ml-2" x-text="'MVR ' + parseFloat(item.price).toFixed(2)"></span>
                                        </div>
                                        <div class="flex gap-2">
                                            <button @click="moveToShared(index)" class="text-purple-600 hover:text-purple-800" title="Move to Shared Items">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                                </svg>
                                            </button>
                                            <button @click="removeItem(index)" class="text-red-600 hover:text-red-800" title="Delete">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="text-sm text-gray-600 mb-2">Assigned to:</div>
                                    <div class="flex flex-wrap gap-2">
                                        <template x-for="person in participants" :key="person">
                                            <label class="inline-flex items-center">
                                                <input type="checkbox" :value="person" x-model="item.assigned_to" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                                <span class="ml-2 text-sm" x-text="person"></span>
                                            </label>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Shared Items -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Shared Items (Split Equally)</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <input type="text" x-model="newSharedItem.name" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Item name">
                            <input type="number" x-model="newSharedItem.price" step="0.01" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Price">
                            <button @click="addSharedItem" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg font-medium">
                                Add Shared Item
                            </button>
                        </div>

                        <div class="space-y-2">
                            <template x-for="(item, index) in sharedItems" :key="index">
                                <div class="flex justify-between items-center border border-gray-200 rounded-lg p-3">
                                    <div>
                                        <span class="font-medium" x-text="item.name"></span>
                                        <span class="text-gray-600 ml-2" x-text="'MVR ' + parseFloat(item.price).toFixed(2)"></span>
                                    </div>
                                    <div class="flex gap-2">
                                        <button @click="moveToPersonal(index)" class="text-blue-600 hover:text-blue-800" title="Move to Personal Items">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7h-12m0 0l4-4m-4 4l4 4m0 6h12m0 0l-4 4m4-4l-4-4"></path>
                                            </svg>
                                        </button>
                                        <button @click="removeSharedItem(index)" class="text-red-600 hover:text-red-800" title="Delete">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Settings -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Charges & Taxes</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Service Charge (%)</label>
                                <input type="number" x-model="billData.service_charge_percentage" step="0.01" min="0" max="100" class="w-full md:w-48 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" x-model="billData.gst_enabled" id="gstEnabled" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                <label for="gstEnabled" class="ml-2 text-sm font-medium text-gray-700">Enable GST (8%)</label>
                            </div>
                            <div x-show="billData.gst_enabled" class="flex items-center ml-6">
                                <input type="checkbox" x-model="billData.gst_on_service" id="gstOnService" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                <label for="gstOnService" class="ml-2 text-sm font-medium text-gray-700">Apply GST on Service Charge</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Calculate Button -->
                <div class="flex justify-end gap-4">
                    <a href="{{ route('rattehin.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-3 rounded-lg font-semibold">
                        Cancel
                    </a>
                    <button @click="submitBill" :disabled="participants.length === 0" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold disabled:bg-gray-400">
                        Calculate & Save Bill
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function billSplitter() {
            return {
                billData: {
                    title: '',
                    restaurant_name: '',
                    service_charge_percentage: 10,
                    gst_enabled: false,
                    gst_on_service: false,
                    currency: 'MVR'
                },
                participants: [],
                newParticipantName: '',
                items: [],
                newItem: { name: '', price: '', assigned_to: [] },
                sharedItems: [],
                newSharedItem: { name: '', price: '' },
                scanning: false,
                scannedItems: [],

                init() {
                    // Initialization
                },

                addParticipant() {
                    if (this.newParticipantName.trim()) {
                        this.participants.push(this.newParticipantName.trim());
                        this.newParticipantName = '';
                    }
                },

                removeParticipant(index) {
                    this.participants.splice(index, 1);
                },

                addItem() {
                    if (this.newItem.name && this.newItem.price) {
                        this.items.push({
                            name: this.newItem.name,
                            price: parseFloat(this.newItem.price),
                            assigned_to: []
                        });
                        this.newItem = { name: '', price: '', assigned_to: [] };
                    }
                },

                removeItem(index) {
                    this.items.splice(index, 1);
                },

                addSharedItem() {
                    if (this.newSharedItem.name && this.newSharedItem.price) {
                        this.sharedItems.push({
                            name: this.newSharedItem.name,
                            price: parseFloat(this.newSharedItem.price)
                        });
                        this.newSharedItem = { name: '', price: '' };
                    }
                },

                removeSharedItem(index) {
                    this.sharedItems.splice(index, 1);
                },

                moveToShared(index) {
                    // Get the item from personal items
                    const item = this.items[index];

                    // Add to shared items (without assigned_to)
                    this.sharedItems.push({
                        name: item.name,
                        price: item.price
                    });

                    // Remove from personal items
                    this.items.splice(index, 1);
                },

                moveToPersonal(index) {
                    // Get the item from shared items
                    const item = this.sharedItems[index];

                    // Add to personal items (with empty assigned_to)
                    this.items.push({
                        name: item.name,
                        price: item.price,
                        assigned_to: []
                    });

                    // Remove from shared items
                    this.sharedItems.splice(index, 1);
                },

                async compressImage(base64Image, maxSizeKB = 800) {
                    return new Promise((resolve) => {
                        const img = new Image();
                        img.onload = () => {
                            const canvas = document.createElement('canvas');
                            let width = img.width;
                            let height = img.height;

                            // Resize if image is too large (max 1600px on longest side)
                            const maxDimension = 1600;
                            if (width > maxDimension || height > maxDimension) {
                                if (width > height) {
                                    height = (height / width) * maxDimension;
                                    width = maxDimension;
                                } else {
                                    width = (width / height) * maxDimension;
                                    height = maxDimension;
                                }
                            }

                            canvas.width = width;
                            canvas.height = height;

                            const ctx = canvas.getContext('2d');
                            ctx.drawImage(img, 0, 0, width, height);

                            // Start with quality 0.8 and reduce if needed
                            let quality = 0.8;
                            let compressedImage = canvas.toDataURL('image/jpeg', quality);

                            // Keep reducing quality until under maxSizeKB
                            while (compressedImage.length > maxSizeKB * 1024 && quality > 0.1) {
                                quality -= 0.1;
                                compressedImage = canvas.toDataURL('image/jpeg', quality);
                            }

                            resolve(compressedImage);
                        };
                        img.src = base64Image;
                    });
                },

                async handleImageUpload(event) {
                    const file = event.target.files[0];
                    if (!file) return;

                    this.scanning = true;
                    this.scannedItems = [];

                    const reader = new FileReader();
                    reader.onload = async (e) => {
                        try {
                            // Compress the image before uploading
                            const compressedImage = await this.compressImage(e.target.result);

                            console.log('Original size:', (e.target.result.length / 1024).toFixed(2), 'KB');
                            console.log('Compressed size:', (compressedImage.length / 1024).toFixed(2), 'KB');

                            const response = await fetch('{{ route("rattehin.scan") }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    image: compressedImage
                                })
                            });

                            const data = await response.json();

                            if (data.success) {
                                this.scannedItems = data.items || [];

                                // Debug: Log OCR results to console
                                console.log('=== OCR SCAN RESULTS ===');
                                console.log('Extracted Text:', data.extractedText);
                                console.log('Parsed Items:', data.items);
                                console.log('========================');

                                // Show extracted text if no items found
                                if (!data.items || data.items.length === 0) {
                                    alert('No items detected. Raw OCR text:\n\n' + data.extractedText + '\n\nPlease check browser console for details.');
                                }
                            } else {
                                alert('Failed to scan bill: ' + (data.error || 'Unknown error'));
                                console.error('OCR Error:', data);
                            }
                        } catch (error) {
                            alert('Error scanning bill: ' + error.message);
                            console.error('Scan Error:', error);
                        } finally {
                            this.scanning = false;
                        }
                    };
                    reader.readAsDataURL(file);
                },

                addAllScannedItems() {
                    this.scannedItems.forEach(item => {
                        // Add to personal items (not shared) with empty assigned_to array
                        this.items.push({
                            name: item.name,
                            price: parseFloat(item.price),
                            assigned_to: []
                        });
                    });
                    this.scannedItems = [];
                    alert(`${this.scannedItems.length || 'All'} items added to Personal Items. Don't forget to assign them to participants!`);
                },

                async submitBill() {
                    if (this.participants.length === 0) {
                        alert('Please add at least one participant');
                        return;
                    }

                    const formData = {
                        ...this.billData,
                        participants: this.participants.map(name => ({ name })),
                        items: this.items,
                        shared_items: this.sharedItems,
                        _token: '{{ csrf_token() }}'
                    };

                    try {
                        const response = await fetch('{{ route("rattehin.store") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify(formData)
                        });

                        if (response.ok) {
                            const data = await response.json();
                            if (data.redirect) {
                                window.location.href = data.redirect;
                            } else {
                                window.location.href = '{{ route("rattehin.index") }}';
                            }
                        } else {
                            const errorData = await response.json();
                            alert('Error: ' + (errorData.message || 'Failed to create bill'));
                        }
                    } catch (error) {
                        console.error('Submission error:', error);
                        alert('Error submitting bill: ' + error.message);
                    }
                }
            };
        }
    </script>
    @endpush
</x-app-layout>
