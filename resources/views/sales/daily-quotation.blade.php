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
            <div class="text-xs">Website: {{ $brand['website'] }}</div>
        </div>
        <div style="text-align:right;">
            <div class="font-bold">QUOTATION</div>
            <div class="text-xs mt-1">Quotation No: {{ $quotationNumber }}</div>
            <div class="text-xs">Sale #{{ $log->id }}</div>
            <div class="text-xs">Date: {{ $log->date->format('Y-m-d') }}</div>
            <div class="mt-1">
                <span class="badge-pending">QUOTATION</span>
            </div>
        </div>
    </div>

    <div style="background: #fef3c7; border: 1px solid #f59e0b; padding: 8px; margin-bottom: 10px; border-radius: 4px;">
        <div class="text-xs font-bold" style="color: #92400e;">Note: This is a quotation, not an invoice. Prices and services are estimates.</div>
    </div>

    <div class="flex justify-between items-start border rounded" style="padding:10px;">
        <div>
            <div class="text-sm font-bold mb-1">Bill To</div>
            @if($log->customer)
                <div class="text-sm">{{ $log->customer->name }}</div>
                <div class="text-xs">Phone: {{ $log->customer->phone }}</div>
            @else
                <div class="text-sm">Walk-in Customer</div>
            @endif
        </div>
        <div style="text-align:right;">
            <div class="text-xs"><strong>Business Unit:</strong> {{ $log->business_unit === 'moto' ? 'Micro Moto' : 'Micro Cool' }}</div>
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

    {{-- Payment Details Footer --}}
    <div class="mt-6" style="border-top: 2px solid #e5e7eb; padding-top: 15px;">
        <div class="text-sm font-bold mb-2">Payment Details</div>
        <div class="text-xs mb-1">
            <strong>Bank Transfer:</strong>
            @if($log->business_unit === 'cool')
                7730000785866
            @else
                7730000140010
            @endif
        </div>
        <div class="text-xs mb-1">
            <strong>Account Name:</strong>
            @if($log->business_unit === 'cool')
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
