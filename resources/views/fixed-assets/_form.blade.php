@if ($errors->any())
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/30 dark:text-red-300">
        Please fix the errors below.
    </div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Asset Code</label>
        <input value="{{ old('asset_code', $asset->asset_code ?: 'Will be generated automatically') }}" class="mt-1 w-full rounded-lg border-gray-300 bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100" readonly>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Generated automatically from category + brand.</p>
        @error('asset_code') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
        <input name="name" value="{{ old('name', $asset->name) }}" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" required>
        @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Category</label>
        <select name="fixed_asset_category_id" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" required>
            <option value="">Select category</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" @selected((string) old('fixed_asset_category_id', $asset->fixed_asset_category_id) === (string) $category->id)>
                    {{ $category->name }} ({{ strtoupper($category->code) }})
                </option>
            @endforeach
        </select>
        @error('fixed_asset_category_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Brand</label>
        <select name="fixed_asset_brand_id" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" required>
            <option value="">Select brand</option>
            @foreach($brands as $brand)
                <option value="{{ $brand->id }}" @selected((string) old('fixed_asset_brand_id', $asset->fixed_asset_brand_id) === (string) $brand->id)>
                    {{ $brand->name }} ({{ strtoupper($brand->code) }})
                </option>
            @endforeach
        </select>
        @error('fixed_asset_brand_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Model</label>
        <input name="model" value="{{ old('model', $asset->model) }}" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
        @error('model') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Serial Number</label>
        <input name="serial_number" value="{{ old('serial_number', $asset->serial_number) }}" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
        @error('serial_number') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Photo</label>
        <input type="file" name="photo" accept="image/*" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
        @error('photo') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        @if($asset->photo_path)
            <img src="{{ \Illuminate\Support\Facades\Storage::url($asset->photo_path) }}" alt="{{ $asset->name }}" class="mt-3 h-24 w-24 rounded-lg object-cover border border-gray-200 dark:border-gray-700">
        @endif
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Condition</label>
        <select name="condition" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
            @foreach($conditionOptions as $condition)
                <option value="{{ $condition }}" @selected(old('condition', $asset->condition ?: \App\Models\FixedAsset::CONDITION_GOOD) === $condition)>{{ $condition }}</option>
            @endforeach
        </select>
        @error('condition') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
        <select name="status" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
            @foreach($statusOptions as $status)
                <option value="{{ $status }}" @selected(old('status', $asset->status ?: \App\Models\FixedAsset::STATUS_AVAILABLE) === $status)>{{ $status }}</option>
            @endforeach
        </select>
        @error('status') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>
    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
        <textarea name="notes" rows="4" class="mt-1 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">{{ old('notes', $asset->notes) }}</textarea>
        @error('notes') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>
</div>
