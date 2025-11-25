<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoiceNumber }}</title>
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
        .badge-paid { display:inline-block; padding:2px 6px; border-radius:9999px; background:#dcfce7; color:#166534; font-size:10px; font-weight:600;}
        .badge-unpaid { display:inline-block; padding:2px 6px; border-radius:9999px; background:#fee2e2; color:#b91c1c; font-size:10px; font-weight:600;}
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
            <div class="text-xs">Website: {{ $brand['website'] }}</div>
        </div>
        <div style="text-align:right;">
            <div class="font-bold">INVOICE</div>
            <div class="text-xs mt-1">Invoice No: {{ $invoiceNumber }}</div>
            <div class="text-xs">Job ID: #{{ $job->id }}</div>
            <div class="text-xs">Date: {{ now()->format('Y-m-d') }}</div>
            <div class="mt-1">
                @if($job->payment_status === 'paid')
                    <span class="badge-paid">PAID</span>
                @else
                    <span class="badge-unpaid">{{ strtoupper($job->payment_status) }}</span>
                @endif
            </div>
        </div>
    </div>

    <div class="flex justify-between items-start border rounded" style="padding:10px;">
        <div>
            <div class="text-sm font-bold mb-1">Bill To</div>
            <div class="text-sm">{{ $job->customer?->name }}</div>
            @if($job->customer?->address)
                <div class="text-xs">{{ $job->customer->address }}</div>
            @endif
            <div class="text-xs">Phone: {{ $job->customer?->phone }}</div>
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
                    <td>{{ $item->inventoryItem?->name ?? 'Service' }}</td>
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
            @forelse($job->items->where('is_service', false) as $item)
                <tr>
                    <td>{{ $item->inventoryItem?->name ?? 'Item' }}</td>
                    <td class="text-right">{{ $item->quantity }}</td>
                    <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right">{{ number_format($item->subtotal, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-xs">No parts listed.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

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

    {{-- Payment Details Footer --}}
    <div class="mt-6" style="border-top: 2px solid #e5e7eb; padding-top: 15px;">
        <div class="text-sm font-bold mb-2">Payment Details</div>
        <div class="text-xs mb-1">
            <strong>Bank Transfer:</strong>
            @if($job->job_type === 'ac')
                7730000785866
            @else
                7730000140010
            @endif
        </div>
        <div class="text-xs mb-1">
            <strong>Account Name:</strong>
            @if($job->job_type === 'ac')
                Hussain M. Ibrahim
            @else
                Micronet
            @endif
        </div>
        <div class="text-xs mb-3">
            After payment, please WhatsApp the receipt to <strong>9996210</strong> for confirmation.
        </div>

        <div class="text-sm font-bold mb-2">Payment Terms</div>
        <ul style="margin: 0; padding-left: 20px; line-height: 1.6;">
            <li class="text-xs">Payment is due upon receipt of invoice.</li>
            <li class="text-xs">Services/products will be considered complete once full payment is received.</li>
            <li class="text-xs">Please ensure the transfer reference matches your invoice number for smooth processing.</li>
            <li class="text-xs">Late or pending payments may delay future service appointments.</li>
        </ul>
    </div>
</div>
</body>
</html>

