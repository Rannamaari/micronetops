@props(['vendors', 'selected' => null])

<div>
    <div class="flex items-center justify-between gap-3">
        <label for="vendor-select" class="block text-sm font-medium">Vendor</label>
        <button type="button" id="open-vendor-modal" class="text-sm text-blue-600 hover:underline">Add Vendor</button>
    </div>
    <input type="search" id="vendor-search" autocomplete="off"
           class="mt-2 w-full rounded border-gray-300 text-sm"
           placeholder="Search vendor by name or phone...">
    <select id="vendor-select" name="vendor_id" class="mt-2 w-full rounded border-gray-300" required>
        <option value="">Select vendor</option>
        @foreach ($vendors as $vendor)
            <option value="{{ $vendor->id }}"
                    data-search="{{ strtolower(trim($vendor->name . ' ' . $vendor->phone . ' ' . $vendor->contact_name . ' ' . $vendor->gst_number)) }}"
                    data-gst-number="{{ $vendor->gst_number }}"
                    @selected((string) old('vendor_id', $selected) === (string) $vendor->id)>
                {{ $vendor->name }}{{ $vendor->phone ? ' (' . $vendor->phone . ')' : '' }}
            </option>
        @endforeach
    </select>
    @error('vendor_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    <p id="vendor-search-empty" class="hidden mt-1 text-xs text-amber-600">No matching vendor. Use Add Vendor to create one.</p>
</div>

@once
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const search = document.getElementById('vendor-search');
            const select = document.getElementById('vendor-select');
            const empty = document.getElementById('vendor-search-empty');
            if (!search || !select) return;

            const filterVendors = () => {
                const query = search.value.trim().toLowerCase();
                let matches = 0;

                Array.from(select.options).forEach((option, index) => {
                    if (index === 0) return;
                    const searchable = option.dataset.search || option.textContent.toLowerCase();
                    const visible = !query || searchable.includes(query) || option.selected;
                    option.hidden = !visible;
                    option.disabled = !visible;
                    if (visible) matches++;
                });

                empty?.classList.toggle('hidden', matches > 0);
            };

            search.addEventListener('input', filterVendors);
            select.addEventListener('change', () => {
                if (select.value) search.value = '';
                filterVendors();
            });
        });
    </script>
@endonce
