@props([
    'isGst' => false,
    'subtotal' => null,
])

@php($gstSelected = (bool) old('is_gst_applicable', $isGst))

<div class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/40">
    <input type="hidden" name="is_gst_applicable" value="0">
    <label class="inline-flex cursor-pointer items-center gap-3">
        <input type="checkbox" id="expense-has-gst" name="is_gst_applicable" value="1"
               class="h-5 w-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500" @checked($gstSelected)>
        <span>
            <span class="block text-sm font-semibold text-gray-900 dark:text-gray-100">GST Tax Invoice (8%)</span>
            <span class="block text-xs text-gray-500 dark:text-gray-400">Adds 8% GST to the amount entered above.</span>
        </span>
    </label>

    <div id="expense-gst-summary" class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-3 {{ $gstSelected ? '' : 'hidden' }}">
        <div class="rounded-lg bg-white p-3 dark:bg-gray-800">
            <p class="text-xs text-gray-500">Before GST</p>
            <p class="mt-1 font-semibold">MVR <span id="expense-gst-subtotal">0.00</span></p>
        </div>
        <div class="rounded-lg bg-blue-50 p-3 dark:bg-blue-900/20">
            <p class="text-xs text-blue-600 dark:text-blue-300">GST 8%</p>
            <p class="mt-1 font-semibold text-blue-700 dark:text-blue-200">MVR <span id="expense-gst-amount">0.00</span></p>
        </div>
        <div class="rounded-lg bg-emerald-50 p-3 dark:bg-emerald-900/20">
            <p class="text-xs text-emerald-700 dark:text-emerald-300">Total Payable</p>
            <p class="mt-1 font-semibold text-emerald-800 dark:text-emerald-200">MVR <span id="expense-gst-total">0.00</span></p>
        </div>
    </div>
    <p id="expense-gst-vendor-warning" class="mt-3 hidden text-sm font-medium text-amber-700 dark:text-amber-300">
        Add the selected vendor's GST TIN before recording this tax invoice.
    </p>
</div>

@once
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const checkbox = document.getElementById('expense-has-gst');
            const amount = document.getElementById('expense-amount');
            const summary = document.getElementById('expense-gst-summary');
            const subtotalOutput = document.getElementById('expense-gst-subtotal');
            const gstOutput = document.getElementById('expense-gst-amount');
            const totalOutput = document.getElementById('expense-gst-total');
            const vendor = document.getElementById('vendor-select');
            const warning = document.getElementById('expense-gst-vendor-warning');
            if (!checkbox || !amount || !summary) return;

            const update = () => {
                const subtotal = Number.parseFloat(amount.value || '0') || 0;
                const gst = checkbox.checked ? Math.round(subtotal * 8) / 100 : 0;
                subtotalOutput.textContent = subtotal.toFixed(2);
                gstOutput.textContent = gst.toFixed(2);
                totalOutput.textContent = (subtotal + gst).toFixed(2);
                summary.classList.toggle('hidden', !checkbox.checked);

                const selectedVendor = vendor?.selectedOptions?.[0];
                const missingTin = checkbox.checked && vendor?.value && !selectedVendor?.dataset?.gstNumber;
                warning?.classList.toggle('hidden', !missingTin);
            };

            checkbox.addEventListener('change', update);
            amount.addEventListener('input', update);
            vendor?.addEventListener('change', update);
            update();
        });
    </script>
@endonce
