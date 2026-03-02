<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AccountController extends Controller
{
    public function index()
    {
        $accounts = Account::orderBy('name')->paginate(25);

        return view('accounts.index', compact('accounts'));
    }

    public function logs(Request $request)
    {
        $query = AccountTransaction::with('account')
            ->latest('occurred_at')
            ->latest('id');

        if ($request->filled('account_id')) {
            $query->where('account_id', $request->account_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('occurred_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('occurred_at', '<=', $request->date_to);
        }

        $transactions = $query->paginate(30)->withQueryString();
        $accounts = Account::orderBy('name')->get();

        return view('accounts.logs', compact('transactions', 'accounts'));
    }

    public function create()
    {
        $types = Account::getTypes();

        return view('accounts.create', compact('types'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:' . implode(',', array_keys(Account::getTypes()))],
            'is_active' => ['nullable', 'boolean'],
            'balance' => ['nullable', 'numeric'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $validated['is_active'] = (bool) $request->input('is_active', 1);
        $openingBalance = (float) ($validated['balance'] ?? 0);
        $validated['balance'] = 0;

        $account = Account::create($validated);

        if ($openingBalance != 0) {
            $this->recordAdjustment($account, $openingBalance, 'Opening balance');
        }

        return redirect()->route('accounts.index')->with('success', 'Account created.');
    }

    public function show(Account $account)
    {
        $transactions = $account->transactions()->latest('occurred_at')->paginate(20);

        return view('accounts.show', compact('account', 'transactions'));
    }

    public function edit(Account $account)
    {
        $types = Account::getTypes();

        return view('accounts.edit', compact('account', 'types'));
    }

    public function update(Request $request, Account $account)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:' . implode(',', array_keys(Account::getTypes()))],
            'is_active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $validated['is_active'] = (bool) $request->input('is_active', 1);

        $account->update($validated);

        return redirect()->route('accounts.index')->with('success', 'Account updated.');
    }

    public function adjust(Request $request, Account $account)
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'not_in:0'],
            'occurred_at' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($account, $validated) {
            $amount = (float) $validated['amount'];
            $this->recordAdjustment($account, $amount, $validated['description'] ?? 'Balance adjustment', $validated['occurred_at']);
        });

        return back()->with('success', 'Balance adjusted.');
    }

    private function recordAdjustment(Account $account, float $amount, string $description, ?string $occurredAt = null): void
    {
        $account->balance = (float) $account->balance + $amount;
        $account->save();

        AccountTransaction::create([
            'account_id' => $account->id,
            'type' => 'adjustment',
            'amount' => $amount,
            'occurred_at' => $occurredAt ?? now()->toDateString(),
            'description' => $description,
            'created_by' => Auth::id(),
        ]);
    }
}
