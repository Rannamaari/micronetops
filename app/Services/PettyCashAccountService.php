<?php

namespace App\Services;

use App\Models\Account;
use App\Models\AccountTransaction;
use App\Models\AccountTransfer;
use App\Models\Expense;
use App\Models\PettyCash;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PettyCashAccountService
{
    public function accountFor(User $user): Account
    {
        $account = Account::where('custodian_user_id', $user->id)
            ->where('is_petty_cash', true)
            ->first();

        if ($account) {
            return $account;
        }

        $openingBalance = PettyCash::legacyUserBalance($user);
        $account = Account::create([
            'name' => 'Petty Cash - ' . $user->name,
            'type' => Account::TYPE_BUSINESS,
            'is_active' => true,
            'is_system' => true,
            'is_petty_cash' => true,
            'custodian_user_id' => $user->id,
            'balance' => $openingBalance,
            'notes' => 'Automatically managed staff petty cash account.',
        ]);

        if ($openingBalance !== 0.0) {
            AccountTransaction::create([
                'account_id' => $account->id,
                'type' => 'opening_balance',
                'amount' => $openingBalance,
                'occurred_at' => now()->toDateString(),
                'description' => 'Opening balance imported from petty cash ledger',
                'created_by' => Auth::id(),
            ]);
        }

        return $account;
    }

    public function topUp(User $user, Account $sourceAccount, float $amount, string $purpose): PettyCash
    {
        return DB::transaction(function () use ($user, $sourceAccount, $amount, $purpose) {
            $source = Account::lockForUpdate()->findOrFail($sourceAccount->id);
            if (!$source->is_active || $source->is_petty_cash) {
                throw new \RuntimeException('Choose an active company account as the funding source.');
            }
            if ((float) $source->balance < $amount) {
                throw new \RuntimeException('The selected source account has insufficient funds.');
            }

            $pettyAccount = $this->accountFor($user);
            $pettyAccount = Account::lockForUpdate()->findOrFail($pettyAccount->id);

            $entry = PettyCash::create([
                'user_id' => Auth::id(),
                'assigned_to' => $user->id,
                'type' => 'topup',
                'amount' => $amount,
                'purpose' => $purpose,
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'paid_at' => now(),
                'source_account_id' => $source->id,
            ]);

            $transfer = AccountTransfer::create([
                'from_account_id' => $source->id,
                'to_account_id' => $pettyAccount->id,
                'amount' => $amount,
                'occurred_at' => now()->toDateString(),
                'notes' => $purpose,
                'related_type' => PettyCash::class,
                'related_id' => $entry->id,
                'created_by' => Auth::id(),
            ]);

            $source->decrement('balance', $amount);
            $pettyAccount->increment('balance', $amount);

            AccountTransaction::create([
                'account_id' => $source->id,
                'type' => 'transfer_out',
                'amount' => -$amount,
                'occurred_at' => now()->toDateString(),
                'description' => 'Petty cash top-up for ' . $user->name,
                'related_type' => AccountTransfer::class,
                'related_id' => $transfer->id,
                'created_by' => Auth::id(),
            ]);

            AccountTransaction::create([
                'account_id' => $pettyAccount->id,
                'type' => 'transfer_in',
                'amount' => $amount,
                'occurred_at' => now()->toDateString(),
                'description' => 'Top-up from ' . $source->name,
                'related_type' => AccountTransfer::class,
                'related_id' => $transfer->id,
                'created_by' => Auth::id(),
            ]);

            return $entry;
        });
    }

    public function syncExpense(Expense $expense, Account $account): void
    {
        $entry = PettyCash::where('expense_id', $expense->id)->first();

        if (!$account->is_petty_cash || !$account->custodian_user_id) {
            if ($entry && $entry->status === 'approved') {
                $entry->update(['status' => 'reversed']);
            }
            return;
        }

        $attributes = [
            'user_id' => Auth::id(),
            'assigned_to' => $account->custodian_user_id,
            'type' => 'expense',
            'amount' => $expense->amount,
            'category' => $expense->category?->name,
            'purpose' => 'Expense #' . $expense->id . ': ' . ($expense->vendor ?: 'Purchase'),
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'paid_at' => $expense->incurred_at,
            'expense_id' => $expense->id,
        ];

        if ($entry) {
            $entry->update($attributes);
        } else {
            PettyCash::create($attributes);
        }
    }

    public function reverseExpense(Expense $expense): void
    {
        PettyCash::where('expense_id', $expense->id)
            ->where('status', 'approved')
            ->update(['status' => 'reversed']);
    }
}
