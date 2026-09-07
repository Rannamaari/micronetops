<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->boolean('is_petty_cash')->default(false)->after('is_system');
            $table->foreignId('custodian_user_id')
                ->nullable()
                ->unique()
                ->after('is_petty_cash')
                ->constrained('users')
                ->nullOnDelete();
        });

        Schema::table('petty_cash', function (Blueprint $table) {
            $table->foreignId('source_account_id')
                ->nullable()
                ->after('source_payment_id')
                ->constrained('accounts')
                ->nullOnDelete();
            $table->foreignId('expense_id')
                ->nullable()
                ->unique()
                ->after('source_account_id')
                ->constrained('expenses')
                ->nullOnDelete();
        });

        $now = now();
        $balances = DB::table('petty_cash')
            ->whereNotNull('assigned_to')
            ->where('status', 'approved')
            ->select('assigned_to')
            ->selectRaw("SUM(CASE WHEN type = 'topup' THEN amount WHEN type = 'expense' THEN -amount ELSE 0 END) AS balance")
            ->groupBy('assigned_to')
            ->get();

        foreach ($balances as $row) {
            $user = DB::table('users')->where('id', $row->assigned_to)->first();
            if (!$user) {
                continue;
            }

            $accountId = DB::table('accounts')->insertGetId([
                'name' => 'Petty Cash - ' . $user->name,
                'type' => 'business',
                'is_active' => true,
                'is_system' => true,
                'is_petty_cash' => true,
                'custodian_user_id' => $user->id,
                'balance' => round((float) $row->balance, 2),
                'notes' => 'Automatically managed staff petty cash account.',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            if ((float) $row->balance !== 0.0) {
                DB::table('account_transactions')->insert([
                    'account_id' => $accountId,
                    'type' => 'opening_balance',
                    'amount' => round((float) $row->balance, 2),
                    'occurred_at' => $now->toDateString(),
                    'description' => 'Opening balance migrated from petty cash ledger',
                    'related_type' => null,
                    'related_id' => null,
                    'created_by' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('petty_cash', function (Blueprint $table) {
            $table->dropConstrainedForeignId('expense_id');
            $table->dropConstrainedForeignId('source_account_id');
        });

        Schema::table('accounts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('custodian_user_id');
            $table->dropColumn('is_petty_cash');
        });
    }
};
