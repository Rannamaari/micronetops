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
        .text-center { text-align: center; }
        .badge-pending { display:inline-block; padding:2px 6px; border-radius:9999px; background:#fef3c7; color:#92400e; font-size:10px; font-weight:600; }
        .badge-gst { display:inline-block; padding:1px 5px; border-radius:9999px; background:#fef3c7; color:#92400e; font-size:9px; font-weight:600; }
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
            <div class="text-xs">Sale #{{ $log->id }}</div>
            <div class="text-xs">Date: {{ $log->date->format('Y-m-d') }}</div>
            <div class="text-xs">Due: {{ $log->due_date ? $log->due_date->format('Y-m-d') : 'Upon receipt' }}</div>
            <div class="mt-1">
                <span class="badge-pending">QUOTATION</span>
            </div>
        </div>
    </div>

    @if($log->notes)
        <div class="mt-2" style="border: 1px solid #e5e7eb; padding: 8px; border-radius: 4px;">
            <div class="text-xs font-bold mb-1">Notes</div>
            <div class="text-xs">{!! nl2br(e($log->notes)) !!}</div>
        </div>
    @endif

	    <div class="flex justify-between items-start border rounded" style="padding:10px;">
	        <div>
	            <div class="text-sm font-bold mb-1">Bill To</div>
	            @if($log->customer)
	                <div class="text-sm">{{ $log->customer->name }}</div>
                    @if($log->customer_address_text)
                        <div class="text-xs">{{ $log->customer_address_text }}</div>
                    @endif
	            @if($log->customer->gst_number)
	                    <div class="text-xs">GST No: {{ $log->customer->gst_number }}</div>
	                @endif
	                <div class="text-xs">Phone: {{ $log->customer->phone }}</div>
                    @if(($log->approval_method ?? 'not_applicable') === 'po' && $log->po_number)
                        <div class="text-xs">PO No: {{ $log->po_number }}</div>
                    @elseif(($log->approval_method ?? 'not_applicable') === 'signed_copy')
                        <div class="text-xs">Approval: Signed copy via WhatsApp</div>
                    @endif
	            @else
	                <div class="text-sm">Walk-in Customer</div>
	            @endif
	        </div>
        <div style="text-align:right;">
	            <div class="text-xs"><strong>Business Unit:</strong> {{ $log->business_unit === 'moto' ? 'Micro Moto' : ($log->business_unit === 'cool' ? 'Micro Cool' : ($log->business_unit === 'easyfix' ? 'Micronet - Easy Fix' : 'Micronet')) }}</div>
        </div>
    </div>

    {{-- Items --}}
    <div class="mt-4">
        <div class="text-sm font-bold mb-1">Items</div>
        <table>
            <thead>
            <tr>
                <th>Description</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Unit Price</th>
                <th class="text-center">GST</th>
                <th class="text-right">Total</th>
            </tr>
            </thead>
            <tbody>
            @forelse($log->lines as $line)
                <tr>
                    <td>
                        {{ $line->description }}
                        @if($line->note)
                            <div class="text-xs" style="color: #6b7280;">{{ $line->note }}</div>
                        @endif
                        @if($line->warranty_value && $line->warranty_unit)
                            <div class="text-xs" style="color: #047857;">
                                Proposed warranty: {{ $line->warranty_value }} {{ $line->warranty_value == 1 ? rtrim($line->warranty_unit, 's') : $line->warranty_unit }}
                            </div>
                        @endif
                    </td>
                    <td class="text-right">{{ $line->qty }}</td>
                    <td class="text-right">{{ number_format($line->unit_price, 2) }}</td>
                    <td class="text-center">
                        @if($line->is_gst_applicable)
                            <span class="badge-gst">8%</span>
                        @else
                            &mdash;
                        @endif
                    </td>
                    <td class="text-right">{{ number_format($line->line_total + $line->gst_amount, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-xs">No items listed.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- Totals --}}
    @php $totals = $log->totals; @endphp
    <div class="mt-4" style="max-width:300px; margin-left:auto;">
        <table>
            <tbody>
            <tr>
                <td>Subtotal</td>
                <td class="text-right">{{ number_format($totals['subtotal'], 2) }} MVR</td>
            </tr>
            @if($totals['gst'] > 0)
            <tr>
                <td>GST (8%)</td>
                <td class="text-right">{{ number_format($totals['gst'], 2) }} MVR</td>
            </tr>
            @endif
            <tr>
                <td class="font-bold border-b">Grand Total</td>
                <td class="text-right font-bold border-b">{{ number_format($totals['grand'], 2) }} MVR</td>
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
            <li class="text-xs">This quotation is valid for {{ $log->quotation_validity_days ?? 3 }} day{{ (($log->quotation_validity_days ?? 3) == 1) ? '' : 's' }} from the quotation date unless otherwise stated.</li>
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
