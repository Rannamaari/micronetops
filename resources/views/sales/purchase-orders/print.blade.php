<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Purchase Order {{ $purchaseOrder->po_number }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; font-size: 12px; color: #111827; margin: 0; padding: 20px; }
        .sheet { max-width: 800px; margin: 0 auto; }
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
        th, td { padding: 6px 8px; border-bottom: 1px solid #e5e7eb; text-align: left; vertical-align: top; }
        th { background: #f9fafb; font-weight: 600; }
        .text-right { text-align: right; }
        .badge { display:inline-block; padding:2px 8px; border-radius:9999px; font-size:10px; font-weight:700; }
        .badge-draft { background:#fef3c7; color:#92400e; }
        .badge-issued { background:#dcfce7; color:#166534; }
        .badge-cancelled { background:#fee2e2; color:#991b1b; }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
<div class="sheet">
    <div class="no-print" style="text-align:right; margin-bottom:10px;">
        <button onclick="window.print()">Print</button>
    </div>

    <div class="flex justify-between items-start mb-4">
        <div>
            <div class="text-lg font-bold">{{ $brand['name'] }}</div>
            <div class="text-xs mt-2">{{ $brand['address'] }}</div>
            <div class="text-xs">Phone: {{ $brand['phone'] }}</div>
            <div class="text-xs">Email: {{ $brand['email'] }}</div>
            <div class="text-xs">Website: {{ $brand['website'] }}</div>
        </div>
        <div style="text-align:right;">
            <div class="font-bold">PURCHASE ORDER</div>
            <div class="text-xs mt-1">PO No: {{ $purchaseOrder->po_number }}</div>
            <div class="text-xs">Date: {{ $purchaseOrder->order_date?->format('Y-m-d') }}</div>
            <div class="text-xs">Expected: {{ $purchaseOrder->expected_date?->format('Y-m-d') ?? 'Not set' }}</div>
            <div class="mt-1">
                <span class="badge {{ $purchaseOrder->status === \App\Models\PurchaseOrder::STATUS_ISSUED ? 'badge-issued' : ($purchaseOrder->status === \App\Models\PurchaseOrder::STATUS_CANCELLED ? 'badge-cancelled' : 'badge-draft') }}">
                    {{ strtoupper($purchaseOrder->status_label) }}
                </span>
            </div>
        </div>
    </div>

    <div class="flex justify-between items-start border rounded" style="padding:10px;">
        <div>
            <div class="text-sm font-bold mb-1">Supplier</div>
            <div class="text-sm">{{ $purchaseOrder->vendor_name }}</div>
            @if($purchaseOrder->vendor_contact_name)
                <div class="text-xs">Contact: {{ $purchaseOrder->vendor_contact_name }}</div>
            @endif
            @if($purchaseOrder->vendor_phone)
                <div class="text-xs">Phone: {{ $purchaseOrder->vendor_phone }}</div>
            @endif
            @if($purchaseOrder->vendor_address)
                <div class="text-xs">{{ $purchaseOrder->vendor_address }}</div>
            @endif
        </div>
        <div style="text-align:right;">
            <div class="text-xs mb-1"><strong>Business Unit:</strong> {{ $brand['name'] }}</div>
            @if($purchaseOrder->reference)
                <div class="text-xs"><strong>Reference:</strong> {{ $purchaseOrder->reference }}</div>
            @endif
            <div class="text-xs"><strong>Prepared By:</strong> {{ $purchaseOrder->creator?->name ?? 'System' }}</div>
            @if($purchaseOrder->issued_at)
                <div class="text-xs"><strong>Issued At:</strong> {{ $purchaseOrder->issued_at->format('d M Y g:i A') }}</div>
            @endif
        </div>
    </div>

    <div class="mt-4">
        <table>
            <thead>
            <tr>
                <th>Description</th>
                <th class="text-right">Qty</th>
                <th>Unit</th>
                <th class="text-right">Unit Cost</th>
                <th class="text-right">Line Total</th>
            </tr>
            </thead>
            <tbody>
            @foreach($purchaseOrder->lines as $line)
                <tr>
                    <td>
                        {{ $line->description }}
                        @if($line->notes)
                            <div class="text-xs" style="color:#6b7280;">{{ $line->notes }}</div>
                        @endif
                    </td>
                    <td class="text-right">{{ number_format($line->quantity, 2) }}</td>
                    <td>{{ $line->unit ?: '—' }}</td>
                    <td class="text-right">{{ number_format($line->unit_cost, 2) }}</td>
                    <td class="text-right">{{ number_format($line->line_total, 2) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    @if($purchaseOrder->notes)
        <div class="mt-4">
            <div class="text-xs font-bold mb-1">Notes</div>
            <div class="text-xs" style="white-space: pre-line;">{{ $purchaseOrder->notes }}</div>
        </div>
    @endif

    @if($purchaseOrder->terms)
        <div class="mt-4">
            <div class="text-xs font-bold mb-1">Terms</div>
            <div class="text-xs" style="white-space: pre-line;">{{ $purchaseOrder->terms }}</div>
        </div>
    @endif

    <div class="mt-4" style="max-width:300px; margin-left:auto;">
        <table>
            <tbody>
            <tr>
                <td>Subtotal</td>
                <td class="text-right">{{ number_format($purchaseOrder->subtotal, 2) }} MVR</td>
            </tr>
            <tr>
                <td class="font-bold border-b">Total</td>
                <td class="text-right font-bold border-b">{{ number_format($purchaseOrder->total_amount, 2) }} MVR</td>
            </tr>
            </tbody>
        </table>
    </div>

    <div class="mt-6 text-xs">
        This is a computer generated purchase order issued by {{ $brand['name'] }}.
    </div>
</div>
</body>
</html>
