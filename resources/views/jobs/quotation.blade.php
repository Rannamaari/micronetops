<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quotation {{ $quotationNumber }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; font-size: 12px; color: #111827; margin: 0; padding: 20px; }
        .invoice { max-width: 800px; margin: 0 auto; }
        .flex { display: flex; }
        .justify-between { justify-content: space-between; }
        .items-start { align-items: flex-start; }
        .mt-2 { margin-top: 0.5rem; }
        .mt-4 { margin-top: 1rem; }
        .mt-6 { margin-top: 1.5rem; }
        .mb-1 { margin-bottom: 0.25rem; }
        .mb-2 { margin-bottom: 0.5rem; }
        .mb-4 { margin-bottom: 1rem; }
        .text-xs { font-size: 11px; }
        .text-sm { font-size: 12px; }
        .text-lg { font-size: 18px; }
        .font-bold { font-weight: 700; }
        .border { border: 1px solid #e5e7eb; }
        .border-b { border-bottom: 1px solid #e5e7eb; }
        .rounded { border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 6px 8px; border-bottom: 1px solid #e5e7eb; text-align: left; }
        th { background: #f9fafb; font-weight: 600; }
        .text-right { text-align: right; }
        .badge-pending { display:inline-block; padding:2px 6px; border-radius:9999px; background:#fef3c7; color:#92400e; font-size:10px; font-weight:600;}
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
<div class="invoice">
    <div class="no-print" style="text-align:right; margin-bottom:10px;">
        <button onclick="window.print()">Print</button>
    </div>

    <div class="flex justify-between items-start mb-4">
	        <div>
	            <div class="text-lg font-bold">{{ $brand['name'] }}</div>
	            <div class="text-xs">{{ $brand['tagline'] }}</div>
	            <div class="text-xs mt-2">{{ $brand['address'] }}</div>
	            <div class="text-xs">Phone: {{ $brand['phone'] }}</div>
	            @if(isset($brand['email']))
	            <div class="text-xs">Email: {{ $brand['email'] }}</div>
	            @endif
	            <div class="text-xs">GST No: 1063676GST501</div>
	            <div class="text-xs">Website: {{ $brand['website'] }}</div>
	        </div>
        <div style="text-align:right;">
            <div class="font-bold">QUOTATION</div>
            <div class="text-xs mt-1">Quotation No: {{ $quotationNumber }}</div>
            <div class="text-xs">Job ID: #{{ $job->id }}</div>
            <div class="text-xs">Date: {{ $job->job_date ? \Carbon\Carbon::parse($job->job_date)->format('Y-m-d') : now()->format('Y-m-d') }}</div>
            <div class="text-xs">Due: {{ $job->due_date ? $job->due_date->format('Y-m-d') : 'Upon receipt' }}</div>
            <div class="mt-1">
                <span class="badge-pending">QUOTATION</span>
            </div>
        </div>
    </div>

	    <div class="flex justify-between items-start border rounded" style="padding:10px;">
	        <div>
	            <div class="text-sm font-bold mb-1">Bill To</div>
	            <div class="text-sm">{{ $job->customer_name ?? $job->customer?->name }}</div>
	            @if($job->address)
	                <div class="text-xs">{{ $job->address }}</div>
	            @endif
	            @if($job->customer?->gst_number)
	                <div class="text-xs">GST No: {{ $job->customer->gst_number }}</div>
	            @endif
	            <div class="text-xs">Phone: {{ $job->customer_phone ?? $job->customer?->phone }}</div>
                @if(($job->approval_method ?? 'not_applicable') === 'po' && $job->po_number)
                    <div class="text-xs">PO No: {{ $job->po_number }}</div>
                @elseif(($job->approval_method ?? 'not_applicable') === 'signed_copy')
                    <div class="text-xs">Approval: Signed copy via WhatsApp</div>
                @endif
	        </div>
        <div style="text-align:right;">
            <div class="text-xs mb-1"><strong>Job Type:</strong> {{ strtoupper($job->job_type) }}</div>
            <div class="text-xs mb-1"><strong>Category:</strong> {{ $job->job_category }}</div>
            @if($job->vehicle)
                <div class="text-xs">
                    <strong>Vehicle:</strong>
                    {{ $job->vehicle->brand }} {{ $job->vehicle->model }}
                    {{ $job->vehicle->registration_number ? '(' . $job->vehicle->registration_number . ')' : '' }}
                </div>
            @endif
            @if($job->acUnit)
                <div class="text-xs">
                    <strong>AC Unit:</strong>
                    {{ $job->acUnit->brand }} {{ $job->acUnit->btu }} BTU ({{ $job->acUnit->gas_type }})
                </div>
            @endif
        </div>
    </div>

    @if($job->problem_description)
        <div class="mt-4">
            <div class="text-xs font-bold mb-1">Job Description</div>
            <div class="text-xs">{{ $job->problem_description }}</div>
        </div>
    @endif

    @if($job->customer_notes)
        <div class="mt-4">
            <div class="text-xs font-bold mb-1">Notes</div>
            <div class="text-xs">{!! nl2br(e($job->customer_notes)) !!}</div>
        </div>
    @endif

    {{-- Services --}}
    <div class="mt-4">
        <div class="text-sm font-bold mb-1">Services (Labour)</div>
        <table>
            <thead>
            <tr>
                <th>Description</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Unit Price</th>
                <th class="text-right">Subtotal</th>
            </tr>
            </thead>
            <tbody>
            @forelse($job->items->where('is_service', true) as $item)
                <tr>
                    <td>
                        {{ $item->item_name ?? $item->inventoryItem?->name ?? 'Service' }}
                        @if($item->item_description)
                            <div class="text-xs" style="color: #6b7280;">{{ $item->item_description }}</div>
                        @endif
                        @if($item->warranty_value && $item->warranty_unit)
                            <div class="text-xs" style="color: #047857;">
                                Proposed warranty: {{ $item->warranty_value }} {{ $item->warranty_value == 1 ? rtrim($item->warranty_unit, 's') : $item->warranty_unit }}
                            </div>
                        @endif
                    </td>
                    <td class="text-right">{{ $item->quantity }}</td>
                    <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right">{{ number_format($item->subtotal, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-xs">No services listed.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @php $partItems = $job->items->where('is_service', false); @endphp
    @if($partItems->count() > 0)
        {{-- Parts --}}
        <div class="mt-4">
            <div class="text-sm font-bold mb-1">Parts & Materials</div>
            <table>
                <thead>
                <tr>
                    <th>Description</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Unit Price</th>
                    <th class="text-right">Subtotal</th>
                </tr>
                </thead>
                <tbody>
                @foreach($partItems as $item)
                    <tr>
                        <td>
                            {{ $item->item_name ?? $item->inventoryItem?->name ?? 'Item' }}
                            @if($item->item_description)
                                <div class="text-xs" style="color: #6b7280;">{{ $item->item_description }}</div>
                            @endif
                            @if($item->warranty_value && $item->warranty_unit)
                                <div class="text-xs" style="color: #047857;">
                                    Proposed warranty: {{ $item->warranty_value }} {{ $item->warranty_value == 1 ? rtrim($item->warranty_unit, 's') : $item->warranty_unit }}
                                </div>
                            @endif
                        </td>
                        <td class="text-right">{{ $item->quantity }}</td>
                        <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-right">{{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Totals --}}
    <div class="mt-4" style="max-width:300px; margin-left:auto;">
        <table>
            <tbody>
            <tr>
                <td>Labour</td>
                <td class="text-right">{{ number_format($job->labour_total, 2) }} MVR</td>
            </tr>
            <tr>
                <td>Travel</td>
                <td class="text-right">{{ number_format($job->travel_charges, 2) }} MVR</td>
            </tr>
            <tr>
                <td>Parts</td>
                <td class="text-right">{{ number_format($job->parts_total, 2) }} MVR</td>
            </tr>
            <tr>
                <td>Discount</td>
                <td class="text-right">-{{ number_format($job->discount, 2) }} MVR</td>
            </tr>
            <tr>
                <td class="font-bold border-b">Total</td>
                <td class="text-right font-bold border-b">
                    {{ number_format($job->total_amount, 2) }} MVR
                </td>
            </tr>
            <tr>
                <td>Paid</td>
                <td class="text-right">{{ number_format($job->paid_amount, 2) }} MVR</td>
            </tr>
            <tr>
                <td>Balance</td>
                <td class="text-right">{{ number_format($job->balance_amount, 2) }} MVR</td>
            </tr>
            </tbody>
        </table>
    </div>

    <div class="mt-6 text-xs">
        Thank you for choosing {{ $brand['name'] }}.
    </div>

    {{-- Footer Terms --}}
    <div class="mt-6" style="border-top: 2px solid #e5e7eb; padding-top: 15px;">
        <div class="text-sm font-bold mb-2">Terms and Conditions</div>
        <ul style="margin: 0; padding-left: 20px; line-height: 1.6;">
            <li class="text-xs">This quotation is an estimate and is not a tax invoice.</li>
            <li class="text-xs">This quotation is valid for {{ $job->quotation_validity_days ?? 3 }} day{{ (($job->quotation_validity_days ?? 3) == 1) ? '' : 's' }} from the quotation date unless otherwise stated.</li>
            <li class="text-xs">To approve this quotation, please issue a Purchase Order referencing this quotation number, or share a signed copy via WhatsApp if a PO cannot be provided.</li>
            <li class="text-xs">Prices, availability, and scope are subject to change until confirmed in writing.</li>
            <li class="text-xs">Any third-party licenses, subscriptions, or hardware are charged separately unless stated otherwise.</li>
            <li class="text-xs">Work will be scheduled once the quotation is approved.</li>
            <li class="text-xs">If additional work is required beyond the quoted scope, we will notify you before proceeding.</li>
        </ul>
    </div>
</div>
</body>
</html>
