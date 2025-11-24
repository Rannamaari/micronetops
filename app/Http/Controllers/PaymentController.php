<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\Payment;
use App\Models\PettyCash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function store(Request $request, Job $job)
    {
        $validated = $request->validate([
            'amount'    => ['required', 'numeric', 'min:0.01'],
            'method'    => ['required', 'string', 'max:50'], // cash, card, transfer, credit
            'reference' => ['nullable', 'string', 'max:255'],
        ]);

        $payment = Payment::create([
            'job_id'    => $job->id,
            'amount'    => $validated['amount'],
            'method'    => $validated['method'],
            'reference' => $validated['reference'] ?? null,
            'status'    => 'completed',
        ]);

        // If payment is cash, automatically add to petty cash as top-up
        if (strtolower($validated['method']) === 'cash') {
            $job->load('customer');
            PettyCash::create([
                'user_id'          => Auth::id(),
                'type'             => 'topup',
                'amount'           => $validated['amount'],
                'category'         => 'customer_payment',
                'purpose'          => 'Cash payment from job #' . $job->id . ($job->customer ? ' - ' . $job->customer->name : ''),
                'status'           => 'approved',
                'approved_by'      => Auth::id(),
                'paid_at'          => now(),
                'source_payment_id' => $payment->id,
            ]);
        }

        $job->updatePaymentStatus();

        return back()->with('success', 'Payment recorded.' . (strtolower($validated['method']) === 'cash' ? ' Cash added to petty cash.' : ''));
    }

    public function destroy(Job $job, Payment $payment)
    {
        // Only admins/managers can delete payments
        $user = Auth::user();
        if (!$user->hasAnyRole(['admin', 'manager'])) {
            abort(403, 'Unauthorized. Admin or Manager access required to delete payments.');
        }

        if ($payment->job_id !== $job->id) {
            abort(404);
        }

        // If this was a cash payment, remove the associated petty cash top-up
        if (strtolower($payment->method) === 'cash') {
            PettyCash::where('source_payment_id', $payment->id)->delete();
        }

        // Delete payment
        $payment->delete();

        $job->updatePaymentStatus();

        return back()->with('success', 'Payment removed.');
    }
}
