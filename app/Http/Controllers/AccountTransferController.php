<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountTransaction;
use App\Models\AccountTransfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AccountTransferController extends Controller
{
    public function index()
    {
        $transfers = AccountTransfer::with(['fromAccount', 'toAccount'])
            ->latest('occurred_at')
            ->paginate(25);

        return view('accounts.transfers.index', compact('transfers'));
    }

    public function create()
    {
        $accounts = Account::where('is_active', true)
            ->where('is_system', false)
            ->orderBy('name')
            ->get();

        return view('accounts.transfers.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'from_account_id' => ['required', 'exists:accounts,id', 'different:to_account_id'],
            'to_account_id' => ['required', 'exists:accounts,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'occurred_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($validated) {
            $from = Account::lockForUpdate()->find($validated['from_account_id']);
            $to = Account::lockForUpdate()->find($validated['to_account_id']);

            $amount = (float) $validated['amount'];

            $transfer = AccountTransfer::create([
                'from_account_id' => $from->id,
                'to_account_id' => $to->id,
                'amount' => $amount,
                'occurred_at' => $validated['occurred_at'],
                'notes' => $validated['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            $from->balance = (float) $from->balance - $amount;
            $from->save();

            $to->balance = (float) $to->balance + $amount;
            $to->save();

            AccountTransaction::create([
                'account_id' => $from->id,
                'type' => 'transfer_out',
                'amount' => -$amount,
                'occurred_at' => $validated['occurred_at'],
                'description' => 'Transfer to ' . $to->name,
                'related_type' => AccountTransfer::class,
                'related_id' => $transfer->id,
                'created_by' => Auth::id(),
            ]);

            AccountTransaction::create([
                'account_id' => $to->id,
                'type' => 'transfer_in',
                'amount' => $amount,
                'occurred_at' => $validated['occurred_at'],
                'description' => 'Transfer from ' . $from->name,
                'related_type' => AccountTransfer::class,
                'related_id' => $transfer->id,
                'created_by' => Auth::id(),
            ]);
        });

        return redirect()->route('accounts.transfers.index')->with('success', 'Transfer recorded.');
    }
}
