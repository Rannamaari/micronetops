{{-- resources/views/jobs/show.blade.php - Mobile-First Optimized --}}

<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Job #{{ $job->id }}
            </h2>
            {{-- WhatsApp quick action in header --}}
            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $job->customer_phone) }}"
               class="inline-flex items-center px-3 py-2 bg-green-500 text-white rounded-lg text-sm font-medium">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
                <span class="hidden sm:inline">WhatsApp</span>
            </a>
        </div>
    </x-slot>

    <div class="py-3 sm:py-6">
        <div class="max-w-5xl mx-auto px-3 sm:px-4 lg:px-8 space-y-4 sm:space-y-6">

            @if (session('success'))
                <div class="px-4 py-3 bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-200 rounded-lg text-sm">
                    {{ session('success') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="px-4 py-3 bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-200 rounded-lg text-sm">
                    {{ $errors->first() }}
                </div>
            @endif

            {{-- Job header - Mobile optimized --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl overflow-hidden">
                {{-- Status & Type bar --}}
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100 dark:border-gray-700
                            {{ $job->job_type === 'ac' ? 'bg-sky-50 dark:bg-sky-900/20' : 'bg-orange-50 dark:bg-orange-900/20' }}">
                    <div class="flex items-center gap-2">
                        <span class="px-3 py-1 rounded-lg text-sm font-semibold
                            {{ $job->job_type === 'moto' ? 'bg-orange-500 text-white' : 'bg-sky-500 text-white' }}">
                            {{ $job->job_type === 'ac' ? 'AC' : 'Bike' }}
                        </span>
                        @if($job->priority && $job->priority !== 'normal')
                            <span class="px-2 py-1 rounded-lg text-xs font-semibold"
                                  style="background-color: {{ $job->priority_color }}; color: white;">
                                {{ ucfirst($job->priority) }}
                            </span>
                        @endif
                    </div>
                    <span class="inline-flex px-3 py-1.5 rounded-lg text-sm font-semibold"
                          style="background-color: {{ $job->status_color }}; color: white;">
                        {{ \App\Models\Job::getStatuses()[$job->status] ?? $job->status }}
                    </span>
                </div>

                <div class="p-4 space-y-4">
                    {{-- Title --}}
                    @if($job->title)
                        <div class="text-lg font-bold text-gray-900 dark:text-gray-100">
                            {{ $job->title }}
                        </div>
                    @endif

                    {{-- Customer info with large touch targets --}}
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">Customer</div>
                            <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                {{ $job->customer_name }}
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                {{ $job->customer_phone }}
                            </div>
                        </div>
                        @if($job->scheduled_at)
                            <div class="text-right flex-shrink-0">
                                <div class="text-xs text-gray-500 dark:text-gray-400">Scheduled</div>
                                <div class="text-sm font-medium {{ $job->scheduled_at->isToday() ? 'text-green-600 dark:text-green-400' : 'text-gray-900 dark:text-gray-100' }}">
                                    {{ $job->scheduled_at->isToday() ? 'Today' : ($job->scheduled_at->isTomorrow() ? 'Tomorrow' : $job->scheduled_at->format('M j')) }}
                                </div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ $job->scheduled_at->format('g:i A') }}
                                </div>
                            </div>
                        @endif
                    </div>

                    @if($job->location)
                        <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                            <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            </svg>
                            {{ $job->location }}
                        </div>
                    @endif

                    {{-- Assigned Technicians --}}
                    @if($job->assignees->count())
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-xs text-gray-500 dark:text-gray-400">Assigned:</span>
                            @foreach($job->assignees as $assignee)
                                <span class="px-2 py-1 bg-indigo-100 dark:bg-indigo-900/50 rounded-lg text-xs font-medium text-indigo-700 dark:text-indigo-300">
                                    {{ $assignee->name }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Status Change Buttons - Full width on mobile --}}
                <div class="px-4 pb-4 pt-2 border-t border-gray-100 dark:border-gray-700">
                    <div class="grid grid-cols-2 sm:flex sm:flex-wrap gap-2">
                        @if(in_array($job->status, ['new', 'scheduled']))
                            <form method="POST" action="{{ route('jobs.update-status', $job) }}" class="col-span-2 sm:col-span-1">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="in_progress">
                                <button type="submit"
                                        class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-3 sm:py-2 bg-amber-500 border border-transparent rounded-xl sm:rounded-lg font-semibold text-sm text-white hover:bg-amber-600 active:bg-amber-700">
                                    <svg class="w-5 h-5 mr-2 sm:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                                    </svg>
                                    Start Job
                                </button>
                            </form>
                        @endif

                        @if($job->status === 'in_progress')
                            <form method="POST" action="{{ route('jobs.update-status', $job) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="waiting_parts">
                                <button type="submit"
                                        class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-3 sm:py-2 bg-red-500 border border-transparent rounded-xl sm:rounded-lg font-semibold text-sm text-white hover:bg-red-600 active:bg-red-700">
                                    Waiting Parts
                                </button>
                            </form>
                            <form method="POST" action="{{ route('jobs.update-status', $job) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="completed">
                                <button type="submit"
                                        class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-3 sm:py-2 bg-green-500 border border-transparent rounded-xl sm:rounded-lg font-semibold text-sm text-white hover:bg-green-600 active:bg-green-700">
                                    <svg class="w-5 h-5 mr-2 sm:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Complete
                                </button>
                            </form>
                        @endif

                        @if($job->status === 'waiting_parts')
                            <form method="POST" action="{{ route('jobs.update-status', $job) }}" class="col-span-2 sm:col-span-1">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="in_progress">
                                <button type="submit"
                                        class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-3 sm:py-2 bg-amber-500 border border-transparent rounded-xl sm:rounded-lg font-semibold text-sm text-white hover:bg-amber-600 active:bg-amber-700">
                                    Resume Work
                                </button>
                            </form>
                        @endif

                        @if($job->isActive())
                            <form method="POST" action="{{ route('jobs.update-status', $job) }}"
                                  onsubmit="return confirm('Cancel this job?')" class="sm:ml-auto">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="cancelled">
                                <button type="submit"
                                        class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-3 sm:py-2 bg-gray-400 border border-transparent rounded-xl sm:rounded-lg font-semibold text-sm text-white hover:bg-gray-500 active:bg-gray-600">
                                    Cancel Job
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                <div class="p-4 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm border-t border-gray-100 dark:border-gray-700">
                    @if($job->vehicle)
                        <div>
                            <div class="text-gray-500 dark:text-gray-400">Vehicle</div>
                            <div class="text-gray-900 dark:text-gray-100">
                                {{ $job->vehicle->brand }} {{ $job->vehicle->model }}
                                {{ $job->vehicle->registration_number ? '(' . $job->vehicle->registration_number . ')' : '' }}
                            </div>
                        </div>
                    @endif
                    @if($job->acUnit)
                        <div>
                            <div class="text-gray-500 dark:text-gray-400">AC Unit</div>
                            <div class="text-gray-900 dark:text-gray-100">
                                {{ $job->acUnit->brand }} - {{ $job->acUnit->btu }} BTU ({{ $job->acUnit->gas_type }})
                                @if($job->acUnit->location_description)
                                    • {{ $job->acUnit->location_description }}
                                @endif
                            </div>
                        </div>
                    @endif
                    @if($job->address)
                        <div class="sm:col-span-2">
                            <div class="text-gray-500 dark:text-gray-400">Address / Location</div>
                            <div class="text-gray-900 dark:text-gray-100">{{ $job->address }}</div>
                        </div>
                    @endif
                    @if($job->pickup_location)
                        <div class="sm:col-span-2">
                            <div class="text-gray-500 dark:text-gray-400">Pickup Location</div>
                            <div class="text-gray-900 dark:text-gray-100">{{ $job->pickup_location }}</div>
                        </div>
                    @endif
                </div>

                @if($job->problem_description)
                    <div class="text-sm">
                        <div class="text-gray-500 dark:text-gray-400 mb-1">Problem / Notes</div>
                        <div class="text-gray-900 dark:text-gray-100 whitespace-pre-line">
                            {{ $job->problem_description }}
                        </div>
                    </div>
                @endif
            </div>

            {{-- Charges: labour (read-only), travel, discount --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">
                    Charges
                </h3>
                <form method="POST" action="{{ route('jobs.update', $job) }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Labour (from services)
                        </label>
                        <input type="text"
                               value="{{ number_format($job->labour_total, 2) }} MVR"
                               class="block w-full rounded-md border-gray-300 text-sm bg-gray-100 dark:bg-gray-700
                                      text-gray-700 dark:text-gray-200"
                               disabled>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Travel (MVR)
                        </label>
                        <input type="number" step="0.01" name="travel_charges"
                               value="{{ old('travel_charges', $job->travel_charges) }}"
                               class="block w-full rounded-md border-gray-300 text-sm
                                      focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Discount (MVR)
                        </label>
                        <input type="number" step="0.01" name="discount"
                               value="{{ old('discount', $job->discount) }}"
                               class="block w-full rounded-md border-gray-300 text-sm
                                      focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div class="flex justify-end sm:justify-start">
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md
                                       font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700
                                       focus:outline-none">
                            Update
                        </button>
                    </div>
                </form>
            </div>

            {{-- Services (labour items) --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6 space-y-4">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                    Services (Labour)
                </h3>

                {{-- Existing service lines --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full text-xs divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 dark:text-gray-400">Service</th>
                            <th class="px-2 py-1 text-right font-medium text-gray-500 dark:text-gray-400">Qty</th>
                            <th class="px-2 py-1 text-right font-medium text-gray-500 dark:text-gray-400">Unit Price</th>
                            <th class="px-2 py-1 text-right font-medium text-gray-500 dark:text-gray-400">Subtotal</th>
                            <th class="px-2 py-1"></th>
                        </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($job->items->where('is_service', true) as $item)
                            <tr>
                                <td class="px-2 py-1">
                                    <div>{{ $item->item_name ?? $item->inventoryItem?->name ?? 'Service #' . $item->inventory_item_id }}</div>
                                    @if($item->item_description)
                                        <div class="text-gray-500 dark:text-gray-400">{{ $item->item_description }}</div>
                                    @endif
                                </td>
                                <td class="px-2 py-1 text-right">{{ $item->quantity }}</td>
                                <td class="px-2 py-1 text-right">{{ number_format($item->unit_price, 2) }}</td>
                                <td class="px-2 py-1 text-right">{{ number_format($item->subtotal, 2) }}</td>
                                <td class="px-2 py-1 text-right">
                                    @if(Auth::user()->canDelete())
                                        <form method="POST" action="{{ route('jobs.items.destroy', [$job, $item]) }}"
                                              onsubmit="return confirm('Remove this service?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="text-xs text-red-600 dark:text-red-400 hover:underline">
                                                Remove
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-gray-400 dark:text-gray-600">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-2 py-2 text-center text-gray-500 dark:text-gray-400">
                                    No services added yet.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Add service --}}
                <form method="POST" action="{{ route('jobs.items.store', $job) }}" class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
                    @csrf
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Service
                        </label>
                        <select id="service-select" name="inventory_item_id"
                                class="block w-full rounded-md border-gray-300 text-sm
                                       focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Select service</option>
                            @foreach($inventoryItems->where('is_service', true) as $inv)
                                <option value="{{ $inv->id }}">
                                    {{ $inv->name }} • {{ number_format($inv->sell_price, 2) }} MVR
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Quantity
                        </label>
                        <input type="number" name="quantity" min="1" value="1"
                               class="block w-full rounded-md border-gray-300 text-sm
                                      focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Unit Price (optional)
                        </label>
                        <input type="number" step="0.01" name="unit_price"
                               class="block w-full rounded-md border-gray-300 text-sm
                                      focus:border-indigo-500 focus:ring-indigo-500"
                               placeholder="Default = service price">
                    </div>

                    <div class="md:col-span-4 flex justify-end">
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md
                                       font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700
                                       focus:outline-none">
                            Add Service
                        </button>
                    </div>
                </form>
            </div>

            {{-- Parts & Materials (non-service items) --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6 space-y-4">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                    Parts & Materials
                </h3>

                {{-- Existing parts --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full text-xs divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 dark:text-gray-400">Item</th>
                            <th class="px-2 py-1 text-right font-medium text-gray-500 dark:text-gray-400">Qty</th>
                            <th class="px-2 py-1 text-right font-medium text-gray-500 dark:text-gray-400">Unit Price</th>
                            <th class="px-2 py-1 text-right font-medium text-gray-500 dark:text-gray-400">Subtotal</th>
                            <th class="px-2 py-1"></th>
                        </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($job->items->where('is_service', false) as $item)
                            <tr>
                                <td class="px-2 py-1">
                                    <div>{{ $item->item_name ?? $item->inventoryItem?->name ?? 'Item #' . $item->inventory_item_id }}</div>
                                    @if($item->item_description)
                                        <div class="text-gray-500 dark:text-gray-400">{{ $item->item_description }}</div>
                                    @endif
                                </td>
                                <td class="px-2 py-1 text-right">{{ $item->quantity }}</td>
                                <td class="px-2 py-1 text-right">{{ number_format($item->unit_price, 2) }}</td>
                                <td class="px-2 py-1 text-right">{{ number_format($item->subtotal, 2) }}</td>
                                <td class="px-2 py-1 text-right">
                                    @if(Auth::user()->canDelete())
                                        <form method="POST" action="{{ route('jobs.items.destroy', [$job, $item]) }}"
                                              onsubmit="return confirm('Remove this item?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="text-xs text-red-600 dark:text-red-400 hover:underline">
                                                Remove
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-gray-400 dark:text-gray-600">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-2 py-2 text-center text-gray-500 dark:text-gray-400">
                                    No parts added yet.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Add part --}}
                <form method="POST" action="{{ route('jobs.items.store', $job) }}" class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
                    @csrf

                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Inventory Item
                        </label>
                        <select id="parts-select" name="inventory_item_id"
                                class="block w-full rounded-md border-gray-300 text-sm
                                       focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Select item</option>
                            @foreach($inventoryItems->where('is_service', false) as $inv)
                                <option value="{{ $inv->id }}">
                                    {{ $inv->name }} ({{ $inv->category }}) • Stock: {{ $inv->quantity }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Quantity
                        </label>
                        <input type="number" name="quantity" min="1" value="1"
                               class="block w-full rounded-md border-gray-300 text-sm
                                      focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Unit Price (optional)
                        </label>
                        <input type="number" step="0.01" name="unit_price"
                               class="block w-full rounded-md border-gray-300 text-sm
                                      focus:border-indigo-500 focus:ring-indigo-500"
                               placeholder="Default = item price">
                    </div>

                    <div class="md:col-span-4 flex justify-end">
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md
                                       font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700
                                       focus:outline-none">
                            Add Item
                        </button>
                    </div>
                </form>
            </div>

            {{-- Payments --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6 space-y-4">
                <div class="flex justify-between items-center">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                        Payments
                    </h3>
                    <span class="text-xs px-2 py-1 rounded
                        @if($job->payment_status === 'paid')
                            bg-green-100 text-green-800
                        @elseif($job->payment_status === 'partial')
                            bg-yellow-100 text-yellow-800
                        @else
                            bg-red-100 text-red-800
                        @endif">
                        {{ strtoupper($job->payment_status) }}
                    </span>
                </div>

                {{-- Payment summary --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm">
                    <div>
                        <div class="text-gray-500 dark:text-gray-400">Total Amount</div>
                        <div class="font-semibold text-gray-900 dark:text-gray-100">
                            {{ number_format($job->total_amount, 2) }} MVR
                        </div>
                    </div>
                    <div>
                        <div class="text-gray-500 dark:text-gray-400">Paid</div>
                        <div class="font-semibold text-gray-900 dark:text-gray-100">
                            {{ number_format($job->paid_amount, 2) }} MVR
                        </div>
                    </div>
                    <div>
                        <div class="text-gray-500 dark:text-gray-400">Balance</div>
                        <div class="font-semibold text-gray-900 dark:text-gray-100">
                            {{ number_format($job->balance_amount, 2) }} MVR
                        </div>
                    </div>
                </div>

                {{-- Existing payments list --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full text-xs divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 dark:text-gray-400">Date</th>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 dark:text-gray-400">Method</th>
                            <th class="px-2 py-1 text-right font-medium text-gray-500 dark:text-gray-400">Amount</th>
                            <th class="px-2 py-1 text-left font-medium text-gray-500 dark:text-gray-400">Reference</th>
                            <th class="px-2 py-1"></th>
                        </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($job->payments as $payment)
                            <tr>
                                <td class="px-2 py-1">
                                    {{ $payment->created_at?->format('Y-m-d H:i') }}
                                </td>
                                <td class="px-2 py-1">
                                    {{ ucfirst($payment->method) }}
                                </td>
                                <td class="px-2 py-1 text-right">
                                    {{ number_format($payment->amount, 2) }} MVR
                                </td>
                                <td class="px-2 py-1">
                                    {{ $payment->reference }}
                                </td>
                                <td class="px-2 py-1 text-right">
                                    @if(Auth::user()->canDelete())
                                        <form method="POST" action="{{ route('jobs.payments.destroy', [$job, $payment]) }}"
                                              onsubmit="return confirm('Remove this payment?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="text-xs text-red-600 dark:text-red-400 hover:underline">
                                                Remove
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-gray-400 dark:text-gray-600">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-2 py-2 text-center text-gray-500 dark:text-gray-400">
                                    No payments recorded yet.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Add payment --}}
                <form method="POST" action="{{ route('jobs.payments.store', $job) }}"
                      class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
                    @csrf

                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Amount (MVR)
                        </label>
                        <input type="number" step="0.01" name="amount"
                               value="{{ old('amount', $job->balance_amount) }}"
                               class="block w-full rounded-md border-gray-300 text-sm
                                      focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Method
                        </label>
                        <select name="method"
                                class="block w-full rounded-md border-gray-300 text-sm
                                       focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="transfer">Bank Transfer</option>
                            <option value="credit">Customer Credit</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Reference (optional)
                        </label>
                        <input type="text" name="reference"
                               value="{{ old('reference') }}"
                               class="block w-full rounded-md border-gray-300 text-sm
                                      focus:border-indigo-500 focus:ring-indigo-500"
                               placeholder="Slip no / txn id">
                    </div>

                    <div class="flex justify-end md:justify-start">
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md
                                       font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700
                                       focus:outline-none">
                            Add Payment
                        </button>
                    </div>
                </form>
            </div>

            {{-- Notes Timeline - Mobile optimized --}}
            @if($job->notes->count() || $job->isActive())
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100">
                        Activity
                    </h3>
                </div>

                <div class="p-4 space-y-4">
                    {{-- Add note form - larger on mobile --}}
                    @if($job->isActive())
                    <form method="POST" action="{{ route('jobs.add-note', $job) }}" class="flex gap-2">
                        @csrf
                        <input type="text" name="content"
                               class="flex-1 rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-base py-3 px-4"
                               placeholder="Add a note..." required>
                        <button type="submit"
                                class="px-4 py-3 bg-indigo-600 text-white rounded-xl text-sm font-semibold hover:bg-indigo-700 active:bg-indigo-800">
                            Add
                        </button>
                    </form>
                    @endif

                    {{-- Notes list --}}
                    <div class="space-y-4">
                        @forelse($job->notes as $note)
                            <div class="flex gap-3">
                                <div class="flex-shrink-0 w-9 h-9 rounded-full flex items-center justify-center
                                    {{ $note->type === 'status_change' ? 'bg-blue-100 text-blue-600 dark:bg-blue-900/50 dark:text-blue-400' : '' }}
                                    {{ $note->type === 'assignment' ? 'bg-green-100 text-green-600 dark:bg-green-900/50 dark:text-green-400' : '' }}
                                    {{ $note->type === 'system' ? 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400' : '' }}
                                    {{ $note->type === 'note' ? 'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-400' : '' }}">
                                    @if($note->type === 'status_change')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                        </svg>
                                    @elseif($note->type === 'assignment')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                                        </svg>
                                    @else
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                        </svg>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm text-gray-900 dark:text-gray-100">{{ $note->content }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        {{ $note->user?->name ?? 'System' }} &middot; {{ $note->created_at->diffForHumans() }}
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-sm text-gray-500 dark:text-gray-400 text-center py-6">
                                No activity yet.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
            @endif

            {{-- Totals summary - Mobile optimized with sticky footer feel --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl overflow-hidden mb-4 sm:mb-0">
                <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100">Summary</h3>
                </div>

                <div class="p-4 space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Labour</span>
                        <span class="text-gray-900 dark:text-gray-100 font-medium">
                            {{ number_format($job->labour_total, 2) }} MVR
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Travel</span>
                        <span class="text-gray-900 dark:text-gray-100 font-medium">
                            {{ number_format($job->travel_charges, 2) }} MVR
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Parts</span>
                        <span class="text-gray-900 dark:text-gray-100 font-medium">
                            {{ number_format($job->parts_total, 2) }} MVR
                        </span>
                    </div>
                    @if($job->discount > 0)
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Discount</span>
                        <span class="text-red-600 dark:text-red-400 font-medium">
                            -{{ number_format($job->discount, 2) }} MVR
                        </span>
                    </div>
                    @endif
                    <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700 flex justify-between">
                        <span class="text-base font-bold text-gray-900 dark:text-gray-100">Total</span>
                        <span class="text-base font-bold text-gray-900 dark:text-gray-100">
                            {{ number_format($job->total_amount, 2) }} MVR
                        </span>
                    </div>

                    {{-- Payment status indicator --}}
                    <div class="flex justify-between items-center pt-2">
                        <span class="text-gray-500 dark:text-gray-400">Balance</span>
                        <span class="text-base font-bold {{ $job->balance_amount > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                            {{ number_format($job->balance_amount, 2) }} MVR
                        </span>
                    </div>
                </div>

                {{-- Action buttons --}}
                <div class="px-4 pb-4 pt-2 flex flex-col sm:flex-row gap-2">
                    @if(in_array($job->status, ['new', 'scheduled']))
                        <a href="{{ route('jobs.quotation', $job) }}" target="_blank"
                           class="flex-1 inline-flex items-center justify-center px-4 py-3 bg-amber-500 rounded-xl font-semibold text-sm text-white hover:bg-amber-600 active:bg-amber-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            View Quotation
                        </a>
                    @else
                        <a href="{{ route('jobs.invoice', $job) }}" target="_blank"
                           class="flex-1 inline-flex items-center justify-center px-4 py-3 bg-gray-800 dark:bg-gray-700 rounded-xl font-semibold text-sm text-white hover:bg-gray-900 active:bg-gray-950">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            View Invoice
                        </a>
                    @endif
                    <a href="{{ route('jobs.index') }}"
                       class="flex-1 sm:flex-none inline-flex items-center justify-center px-4 py-3 bg-gray-100 dark:bg-gray-700 rounded-xl font-semibold text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-200 active:bg-gray-300">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to Jobs
                    </a>
                </div>
            </div>

        </div>
    </div>

    {{-- Include Select2 for searchable dropdowns --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        /* Dark mode styles for Select2 */
        .select2-container--default .select2-selection--single {
            background-color: white;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            height: 38px;
            padding: 4px 8px;
        }
        .dark .select2-container--default .select2-selection--single {
            background-color: #374151;
            border-color: #4b5563;
            color: #f3f4f6;
        }
        .dark .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #f3f4f6;
        }
        .dark .select2-dropdown {
            background-color: #374151;
            border-color: #4b5563;
        }
        .dark .select2-container--default .select2-results__option {
            color: #f3f4f6;
        }
        .dark .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #4f46e5;
        }
        .dark .select2-container--default .select2-search--dropdown .select2-search__field {
            background-color: #1f2937;
            border-color: #4b5563;
            color: #f3f4f6;
        }
        .select2-container {
            width: 100% !important;
        }
    </style>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize Select2 on service dropdown
            $('#service-select').select2({
                placeholder: 'Search or select service...',
                allowClear: true,
                width: '100%'
            });

            // Initialize Select2 on parts dropdown
            $('#parts-select').select2({
                placeholder: 'Search or select item...',
                allowClear: true,
                width: '100%'
            });

            // When user clicks on service section, auto-open the dropdown
            $('#service-select').on('select2:open', function() {
                setTimeout(function() {
                    document.querySelector('.select2-search__field').focus();
                }, 100);
            });

            // When user clicks on parts section, auto-open the dropdown
            $('#parts-select').on('select2:open', function() {
                setTimeout(function() {
                    document.querySelector('.select2-search__field').focus();
                }, 100);
            });
        });
    </script>
</x-app-layout>
