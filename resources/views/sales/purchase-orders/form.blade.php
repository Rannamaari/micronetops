@php
    $lineItems = old('lines');

    if ($lineItems === null) {
        $lineItems = $purchaseOrder->exists
            ? $purchaseOrder->lines->map(function ($line) {
                return [
                    'inventory_item_id' => $line->inventory_item_id,
                    'description' => $line->description,
                    'quantity' => $line->quantity,
                    'unit' => $line->unit,
                    'unit_cost' => $line->unit_cost,
                    'notes' => $line->notes,
                ];
            })->toArray()
            : [[
                'inventory_item_id' => null,
                'description' => '',
                'quantity' => 1,
                'unit' => 'pcs',
                'unit_cost' => 0,
                'notes' => '',
            ]];
    }
@endphp

<div class="space-y-6"
     x-data="purchaseOrderForm({
        inventoryItems: {{ Js::from($inventoryItems->map(fn ($item) => [
            'id' => $item->id,
            'name' => $item->name,
            'unit' => $item->unit,
            'cost_price' => (float) $item->cost_price,
        ])->values()) }},
        initialLines: {{ Js::from(array_values($lineItems)) }}
     })">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-2xl p-5 space-y-4">
            <div>
                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Supplier & Unit</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Choose the business unit and supplier details for this PO.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">PO Number</label>
                    <input type="text" name="po_number" value="{{ old('po_number', $purchaseOrder->po_number) }}"
                           placeholder="Leave blank to auto-generate"
                           class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">You can enter your own PO number or leave it blank for automatic numbering.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Business Unit</label>
                    <select name="business_unit" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" required>
                        @foreach($businessUnits as $key => $label)
                            <option value="{{ $key }}" @selected(old('business_unit', $purchaseOrder->business_unit) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Supplier</label>
                    <select name="vendor_id"
                            x-model="selectedVendorId"
                            @change="applyVendorSnapshot()"
                            class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                        <option value="">Custom supplier entry</option>
                        @foreach($vendors as $vendor)
                            <option value="{{ $vendor->id }}"
                                    @selected((string) old('vendor_id', $purchaseOrder->vendor_id) === (string) $vendor->id)
                                    data-name="{{ $vendor->name }}"
                                    data-phone="{{ $vendor->phone }}"
                                    data-contact="{{ $vendor->contact_name }}"
                                    data-address="{{ $vendor->address }}">{{ $vendor->name }}{{ $vendor->phone ? ' (' . $vendor->phone . ')' : '' }}</option>
                        @endforeach
                    </select>
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                        Need a new supplier? <a href="{{ route('vendors.create') }}" target="_blank" class="text-indigo-600 hover:underline dark:text-indigo-400">Add vendor</a>
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Supplier Name</label>
                    <input type="text" name="vendor_name" x-model="vendorName" value="{{ old('vendor_name', $purchaseOrder->vendor_name) }}"
                           class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Supplier Phone</label>
                    <input type="text" name="vendor_phone" x-model="vendorPhone" value="{{ old('vendor_phone', $purchaseOrder->vendor_phone) }}"
                           class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Contact Person</label>
                    <input type="text" name="vendor_contact_name" x-model="vendorContactName" value="{{ old('vendor_contact_name', $purchaseOrder->vendor_contact_name) }}"
                           class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Reference</label>
                    <input type="text" name="reference" value="{{ old('reference', $purchaseOrder->reference) }}"
                           placeholder="Quotation / RFQ / internal reference"
                           class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Supplier Address</label>
                <textarea name="vendor_address" rows="3" x-model="vendorAddress"
                          class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">{{ old('vendor_address', $purchaseOrder->vendor_address) }}</textarea>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-2xl p-5 space-y-4">
            <div>
                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Dates & Notes</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Set the order timeline and any supplier-facing instructions.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Order Date</label>
                    <input type="date" name="order_date" value="{{ old('order_date', optional($purchaseOrder->order_date)->format('Y-m-d') ?? now()->toDateString()) }}"
                           class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Expected Date</label>
                    <input type="date" name="expected_date" value="{{ old('expected_date', optional($purchaseOrder->expected_date)->format('Y-m-d')) }}"
                           class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Internal Notes</label>
                <textarea name="notes" rows="4" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                          placeholder="Any remarks for the supplier or internal context...">{{ old('notes', $purchaseOrder->notes) }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Terms</label>
                <textarea name="terms" rows="4" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                          placeholder="Delivery terms, payment terms, or request instructions...">{{ old('terms', $purchaseOrder->terms) }}</textarea>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-2xl p-5 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">PO Lines</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Add the items or services you want to order from the supplier.</p>
            </div>
            <button type="button" @click="addLine()"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition">
                Add Line
            </button>
        </div>

        <template x-if="lines.length === 0">
            <div class="rounded-xl border border-dashed border-gray-300 dark:border-gray-600 p-6 text-sm text-gray-500 dark:text-gray-400 text-center">
                No lines added yet.
            </div>
        </template>

        <template x-for="(line, index) in lines" :key="index">
            <div class="rounded-2xl border border-gray-200 dark:border-gray-700 p-4 space-y-4">
                <div class="flex items-center justify-between gap-3">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100" x-text="`Line ${index + 1}`"></h4>
                    <button type="button" @click="removeLine(index)" class="text-sm text-red-600 hover:text-red-700 dark:text-red-400">Remove</button>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-5 gap-4">
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Inventory Item</label>
                        <select :name="`lines[${index}][inventory_item_id]`" x-model="line.inventory_item_id" @change="fillLineFromInventory(index)"
                                class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                            <option value="">Custom line</option>
                            <template x-for="item in inventoryItems" :key="item.id">
                                <option :value="String(item.id)" x-text="item.name"></option>
                            </template>
                        </select>
                    </div>
                    <div class="lg:col-span-3">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description</label>
                        <input type="text" :name="`lines[${index}][description]`" x-model="line.description"
                               class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" required>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Quantity</label>
                        <input type="number" step="0.01" min="0.01" :name="`lines[${index}][quantity]`" x-model="line.quantity"
                               class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-right" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Unit</label>
                        <input type="text" :name="`lines[${index}][unit]`" x-model="line.unit"
                               class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Unit Cost</label>
                        <input type="number" step="0.01" min="0" :name="`lines[${index}][unit_cost]`" x-model="line.unit_cost"
                               class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-right" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Line Total</label>
                        <div class="h-11 rounded-xl bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 px-4 flex items-center justify-end text-sm font-semibold text-gray-900 dark:text-gray-100" x-text="formatMoney(lineTotal(line))"></div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Line Notes</label>
                    <input type="text" :name="`lines[${index}][notes]`" x-model="line.notes"
                           class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                           placeholder="Optional note for this line">
                </div>
            </div>
        </template>

        <div class="border-t border-gray-200 dark:border-gray-700 pt-4 flex justify-end">
            <div class="w-full sm:w-72 rounded-2xl bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 p-4">
                <div class="flex items-center justify-between text-sm text-gray-600 dark:text-gray-300">
                    <span>Subtotal</span>
                    <span class="font-semibold text-gray-900 dark:text-gray-100" x-text="formatMoney(grandTotal())"></span>
                </div>
                <div class="mt-2 flex items-center justify-between text-base font-bold text-gray-900 dark:text-gray-100">
                    <span>Total</span>
                    <span x-text="formatMoney(grandTotal())"></span>
                </div>
            </div>
        </div>
    </div>

    <script>
        function purchaseOrderForm({ inventoryItems, initialLines }) {
            return {
                inventoryItems,
                selectedVendorId: '{{ old('vendor_id', (string) $purchaseOrder->vendor_id) }}',
                vendorName: @js(old('vendor_name', $purchaseOrder->vendor_name)),
                vendorPhone: @js(old('vendor_phone', $purchaseOrder->vendor_phone)),
                vendorContactName: @js(old('vendor_contact_name', $purchaseOrder->vendor_contact_name)),
                vendorAddress: @js(old('vendor_address', $purchaseOrder->vendor_address)),
                lines: initialLines.map(line => ({
                    inventory_item_id: line.inventory_item_id ? String(line.inventory_item_id) : '',
                    description: line.description ?? '',
                    quantity: line.quantity ?? 1,
                    unit: line.unit ?? 'pcs',
                    unit_cost: line.unit_cost ?? 0,
                    notes: line.notes ?? '',
                })),
                addLine() {
                    this.lines.push({
                        inventory_item_id: '',
                        description: '',
                        quantity: 1,
                        unit: 'pcs',
                        unit_cost: 0,
                        notes: '',
                    });
                },
                removeLine(index) {
                    this.lines.splice(index, 1);
                },
                lineTotal(line) {
                    return (parseFloat(line.quantity || 0) * parseFloat(line.unit_cost || 0));
                },
                grandTotal() {
                    return this.lines.reduce((sum, line) => sum + this.lineTotal(line), 0);
                },
                formatMoney(value) {
                    return `${Number(value || 0).toFixed(2)} MVR`;
                },
                fillLineFromInventory(index) {
                    const selected = this.inventoryItems.find(item => String(item.id) === String(this.lines[index].inventory_item_id));
                    if (!selected) return;
                    if (!this.lines[index].description) this.lines[index].description = selected.name;
                    if (!this.lines[index].unit) this.lines[index].unit = selected.unit || 'pcs';
                    if (!parseFloat(this.lines[index].unit_cost || 0)) this.lines[index].unit_cost = selected.cost_price || 0;
                },
                applyVendorSnapshot() {
                    const select = document.querySelector('select[name="vendor_id"]');
                    const selectedOption = select?.selectedOptions?.[0];
                    if (!selectedOption || !selectedOption.value) return;
                    this.vendorName = selectedOption.dataset.name || this.vendorName;
                    this.vendorPhone = selectedOption.dataset.phone || this.vendorPhone;
                    this.vendorContactName = selectedOption.dataset.contact || this.vendorContactName;
                    this.vendorAddress = selectedOption.dataset.address || this.vendorAddress;
                },
            };
        }
    </script>
</div>
