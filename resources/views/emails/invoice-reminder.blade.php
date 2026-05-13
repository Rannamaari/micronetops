@php
    $dueDate = $job->due_date?->copy()->timezone(config('app.timezone'));
    $today = now()->timezone(config('app.timezone'))->startOfDay();
    $daysOverdue = $dueDate && $today->gt($dueDate->copy()->startOfDay())
        ? $dueDate->copy()->startOfDay()->diffInDays($today)
        : 0;
@endphp
<p>Dear {{ $job->customer_name ?: 'Customer' }},</p>

<p>
    This is a friendly reminder that invoice <strong>{{ $invoiceNumber }}</strong>,
    dated <strong>{{ $invoiceDate->format('d M Y') }}</strong>,
    has an outstanding balance of <strong>MVR {{ number_format((float) $job->balance_amount, 2) }}</strong>.
</p>

@if($dueDate)
    <p>
        The payment due date is <strong>{{ $dueDate->format('d M Y') }}</strong>.
        @if($daysOverdue > 0)
            This invoice is currently overdue by <strong>{{ $daysOverdue }}</strong> day{{ $daysOverdue === 1 ? '' : 's' }}.
        @endif
    </p>
@else
    <p>This invoice is due upon receipt.</p>
@endif

<p>
    Kindly deposit the payment to:
</p>

<p>
    <strong>Account Name:</strong> {{ $paymentDetails['account_name'] }}<br>
    <strong>Account Number:</strong> {{ $paymentDetails['account_number'] }}
</p>

<p>
    After payment, please share your transfer reference or receipt for confirmation.
</p>

<p>
    If you have already made the payment, please disregard this message.
</p>

<p>Thank you,<br>{{ $paymentDetails['label'] }}</p>
