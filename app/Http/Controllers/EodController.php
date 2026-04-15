<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountTransaction;
use App\Models\AccountTransfer;
use App\Models\DailySalesLog;
use App\Models\EodReconciliation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EodController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::user()->canRunEod()) {
            abort(403);
        }

        $date = $request->get('date', now()->toDateString());

        $units = ['moto', 'cool', 'it'];
        $unitData = [];

        foreach ($units as $unit) {
            $eod = EodReconciliation::forDate($date)->forUnit($unit)->first();

            $submittedSales = DailySalesLog::forDate($date)->forUnit($unit)->submitted()->with('lines')->get();
            $draftSales = DailySalesLog::forDate($date)->forUnit($unit)->draft()->get();

            $cashTotal = $submittedSales->where('payment_method', 'cash')->sum(fn($s) => $s->totals['grand']);
            $transferTotal = $submittedSales->where('payment_method', 'transfer')->sum(fn($s) => $s->totals['grand']);

            $unitData[$unit] = [
                'eod' => $eod,
                'submitted_count' => $submittedSales->count(),
                'cash_total' => $cashTotal,
                'transfer_total' => $transferTotal,
                'draft_count' => $draftSales->count(),
            ];
        }

        return view('sales.eod-index', compact('date', 'unitData'));
    }

    public function create(Request $request)
    {
        if (!Auth::user()->canRunEod()) {
            abort(403);
        }

        $validated = $request->validate([
            'date' => ['required', 'date'],
            'business_unit' => ['required', 'in:moto,cool,it'],
        ]);

        $existing = EodReconciliation::forDate($validated['date'])
            ->forUnit($validated['business_unit'])
            ->first();

        if ($existing) {
            return redirect()->route('sales.eod.show', $existing)
                ->with('error', 'EOD already exists for this date and unit.');
        }

        $draftCount = DailySalesLog::forDate($validated['date'])
            ->forUnit($validated['business_unit'])
            ->draft()
            ->count();

        if ($draftCount > 0) {
            return back()->with('error', 'Cannot start EOD — there are ' . $draftCount . ' draft sale(s). Submit or delete them first.');
        }

        $eod = EodReconciliation::create([
            'date' => $validated['date'],
            'business_unit' => $validated['business_unit'],
            'status' => 'open',
        ]);

        $eod->calculateExpected();

        return redirect()->route('sales.eod.show', $eod);
    }

    public function show(EodReconciliation $eod)
    {
        $eod->load('closedByUser', 'depositedByUser', 'depositedAccount');

        $submittedSales = DailySalesLog::forDate($eod->date)
            ->forUnit($eod->business_unit)
            ->submitted()
            ->with('lines')
            ->get();

        $salesCount = $submittedSales->count();
        $cashTotal = $submittedSales->where('payment_method', 'cash')->sum(fn($s) => $s->totals['grand']);
        $transferTotal = $submittedSales->where('payment_method', 'transfer')->sum(fn($s) => $s->totals['grand']);
        $grandTotal = $cashTotal + $transferTotal;

        $accounts = Account::where('is_active', true)
            ->where('is_system', false)
            ->orderBy('name')
            ->get();

        return view('sales.eod-show', compact('eod', 'salesCount', 'cashTotal', 'transferTotal', 'grandTotal', 'accounts'));
    }

    public function close(Request $request, EodReconciliation $eod)
    {
        if (!Auth::user()->canRunEod()) {
            abort(403);
        }

        if (!$eod->isOpen()) {
            return back()->with('error', 'EOD is not in open status.');
        }

        $validated = $request->validate([
            'note_500' => ['nullable', 'integer', 'min:0'],
            'note_100' => ['nullable', 'integer', 'min:0'],
            'note_50' => ['nullable', 'integer', 'min:0'],
            'note_20' => ['nullable', 'integer', 'min:0'],
            'note_10' => ['nullable', 'integer', 'min:0'],
            'coin_2' => ['nullable', 'integer', 'min:0'],
            'coin_1' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $denominations = [
            'note_500' => $validated['note_500'] ?? null,
            'note_100' => $validated['note_100'] ?? null,
            'note_50' => $validated['note_50'] ?? null,
            'note_20' => $validated['note_20'] ?? null,
            'note_10' => $validated['note_10'] ?? null,
            'coin_2' => $validated['coin_2'] ?? null,
            'coin_1' => $validated['coin_1'] ?? null,
        ];

        $eod->close($denominations, $validated['notes'] ?? null);

        // Record cash on hand into a system cash account per unit (so deposit can transfer out)
        if ($eod->counted_cash > 0) {
            $cashAccountName = match ($eod->business_unit) {
                'moto' => 'Cash - Micro Moto',
                'cool' => 'Cash - Micro Cool',
                'it' => 'Cash - Micronet',
                default => 'Cash',
            };
            $cashAccount = Account::firstOrCreate(
                ['name' => $cashAccountName],
                ['type' => Account::TYPE_BUSINESS, 'is_active' => true, 'is_system' => true, 'balance' => 0]
            );

            $existingTx = AccountTransaction::where('account_id', $cashAccount->id)
                ->where('type', 'eod_cash_in')
                ->where('related_type', EodReconciliation::class)
                ->where('related_id', $eod->id)
                ->first();

            if (!$existingTx) {
                $amount = (float) $eod->counted_cash;
                $cashAccount->balance = (float) $cashAccount->balance + $amount;
                $cashAccount->save();

                AccountTransaction::create([
                    'account_id' => $cashAccount->id,
                    'type' => 'eod_cash_in',
                    'amount' => $amount,
                    'occurred_at' => $eod->date,
                    'description' => 'EOD cash counted for ' . $eod->date->format('Y-m-d') . ' (' . $eod->business_unit . ')',
                    'related_type' => EodReconciliation::class,
                    'related_id' => $eod->id,
                    'created_by' => Auth::id(),
                ]);
            }
        }

        return redirect()->route('sales.eod.show', $eod)->with('success', 'End of Day closed successfully.');
    }

    public function deposit(Request $request, EodReconciliation $eod)
    {
        if (!Auth::user()->hasAnyRole(['admin', 'manager'])) {
            abort(403);
        }

        if (!$eod->isClosed()) {
            return back()->with('error', 'EOD must be closed before marking as deposited.');
        }

        $validated = $request->validate([
            'account_id' => ['required', 'exists:accounts,id'],
        ]);

        $amount = (float) $eod->counted_cash;
        if ($amount <= 0) {
            return back()->with('error', 'No cash amount available to deposit.');
        }

        $depositAccount = Account::find($validated['account_id']);

        $cashAccountName = match ($eod->business_unit) {
            'moto' => 'Cash - Micro Moto',
            'cool' => 'Cash - Micro Cool',
            'it' => 'Cash - Micronet',
            default => 'Cash',
        };
        $cashAccount = Account::firstOrCreate(
            ['name' => $cashAccountName],
            ['type' => Account::TYPE_BUSINESS, 'is_active' => true, 'is_system' => true, 'balance' => 0]
        );

        \DB::transaction(function () use ($eod, $amount, $cashAccount, $depositAccount) {
            $transfer = AccountTransfer::create([
                'from_account_id' => $cashAccount->id,
                'to_account_id' => $depositAccount->id,
                'amount' => $amount,
                'occurred_at' => $eod->date,
                'notes' => 'EOD deposit for ' . $eod->date->format('Y-m-d') . ' (' . $eod->business_unit . ')',
                'related_type' => EodReconciliation::class,
                'related_id' => $eod->id,
                'created_by' => Auth::id(),
            ]);

            $cashAccount->balance = (float) $cashAccount->balance - $amount;
            $cashAccount->save();

            $depositAccount->balance = (float) $depositAccount->balance + $amount;
            $depositAccount->save();

            AccountTransaction::create([
                'account_id' => $cashAccount->id,
                'type' => 'transfer_out',
                'amount' => -$amount,
                'occurred_at' => $eod->date,
                'description' => 'EOD deposit to ' . $depositAccount->name,
                'related_type' => AccountTransfer::class,
                'related_id' => $transfer->id,
                'created_by' => Auth::id(),
            ]);

            AccountTransaction::create([
                'account_id' => $depositAccount->id,
                'type' => 'transfer_in',
                'amount' => $amount,
                'occurred_at' => $eod->date,
                'description' => 'EOD deposit from ' . $cashAccount->name,
                'related_type' => AccountTransfer::class,
                'related_id' => $transfer->id,
                'created_by' => Auth::id(),
            ]);

            $eod->deposited_account_id = $depositAccount->id;
            $eod->markDeposited();
        });

        return redirect()->route('sales.eod.show', $eod)->with('success', 'Cash marked as deposited.');
    }

    public function reopen(EodReconciliation $eod)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        if ($eod->isOpen()) {
            return back()->with('error', 'EOD is already open.');
        }

        $eod->reopenEod();

        return redirect()->route('sales.eod.show', $eod)->with('success', 'EOD reopened for re-counting.');
    }
}
