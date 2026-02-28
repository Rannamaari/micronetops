<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\RecurringExpense;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RecurringExpenseController extends Controller
{
    public function index()
    {
        $recurring = RecurringExpense::with(['category', 'vendor'])
            ->orderBy('name')
            ->paginate(25);

        return view('recurring_expenses.index', compact('recurring'));
    }

    public function create()
    {
        $categories = ExpenseCategory::where('is_active', true)->orderBy('name')->get();
        $vendors = Vendor::where('is_active', true)->orderBy('name')->get();
        $businessUnits = Expense::getBusinessUnits();
        $frequencies = RecurringExpense::getFrequencies();

        return view('recurring_expenses.create', compact('categories', 'vendors', 'businessUnits', 'frequencies'));
    }

    public function store(Request $request)
    {
        $validated = $this->validatePayload($request);

        $recurring = RecurringExpense::create($validated);

        return redirect()->route('recurring-expenses.index')
            ->with('success', "Recurring expense '{$recurring->name}' created.");
    }

    public function edit(RecurringExpense $recurringExpense)
    {
        $categories = ExpenseCategory::where('is_active', true)->orderBy('name')->get();
        $vendors = Vendor::where('is_active', true)->orderBy('name')->get();
        $businessUnits = Expense::getBusinessUnits();
        $frequencies = RecurringExpense::getFrequencies();

        return view('recurring_expenses.edit', compact('recurringExpense', 'categories', 'vendors', 'businessUnits', 'frequencies'));
    }

    public function update(Request $request, RecurringExpense $recurringExpense)
    {
        $validated = $this->validatePayload($request);

        $recurringExpense->update($validated);

        return redirect()->route('recurring-expenses.index')
            ->with('success', "Recurring expense '{$recurringExpense->name}' updated.");
    }

    public function generate(Request $request)
    {
        $today = Carbon::today();

        $due = RecurringExpense::where('is_active', true)
            ->whereDate('next_due_at', '<=', $today)
            ->get();

        if ($due->isEmpty()) {
            return redirect()->route('expenses.index')->with('success', 'No recurring expenses due.');
        }

        DB::transaction(function () use ($due, $today) {
            foreach ($due as $template) {
                $dueDate = $template->next_due_at;
                if ($template->last_generated_at && $template->last_generated_at->equalTo($dueDate)) {
                    continue;
                }

                [$vendorId, $vendorName] = $this->resolveVendorForTemplate($template);

                Expense::create([
                    'expense_category_id' => $template->expense_category_id,
                    'vendor_id' => $vendorId,
                    'vendor' => $vendorName,
                    'business_unit' => $template->business_unit,
                    'amount' => $template->amount,
                    'incurred_at' => $dueDate,
                    'reference' => $template->reference,
                    'notes' => $template->notes,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]);

                $template->last_generated_at = $dueDate;
                $template->next_due_at = $this->advanceNextDue($template, $dueDate, $today);
                $template->save();
            }
        });

        return redirect()->route('expenses.index')->with('success', 'Recurring expenses generated.');
    }

    private function validatePayload(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'expense_category_id' => ['required', 'exists:expense_categories,id'],
            'vendor_id' => ['nullable', 'exists:vendors,id'],
            'vendor_name' => ['nullable', 'string', 'max:255'],
            'vendor_phone' => ['nullable', 'string', 'max:50'],
            'vendor_contact_name' => ['nullable', 'string', 'max:255'],
            'vendor_address' => ['nullable', 'string', 'max:255'],
            'business_unit' => ['required', 'in:' . implode(',', array_keys(Expense::getBusinessUnits()))],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'frequency' => ['required', 'in:' . implode(',', array_keys(RecurringExpense::getFrequencies()))],
            'day_of_week' => ['nullable', 'integer', 'min:0', 'max:6'],
            'day_of_month' => ['nullable', 'integer', 'min:1', 'max:31'],
            'next_due_at' => ['required', 'date'],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($validated['frequency'] === RecurringExpense::FREQ_WEEKLY && $validated['day_of_week'] === null) {
            $validated['day_of_week'] = Carbon::parse($validated['next_due_at'])->dayOfWeek;
        }

        if ($validated['frequency'] === RecurringExpense::FREQ_MONTHLY && $validated['day_of_month'] === null) {
            $validated['day_of_month'] = (int) Carbon::parse($validated['next_due_at'])->day;
        }

        if ($validated['frequency'] === RecurringExpense::FREQ_WEEKLY) {
            $validated['day_of_month'] = null;
        }

        if ($validated['frequency'] === RecurringExpense::FREQ_MONTHLY) {
            $validated['day_of_week'] = null;
        }

        $validated['is_active'] = (bool) $request->input('is_active', 1);

        return $validated;
    }

    private function advanceNextDue(RecurringExpense $template, Carbon $currentDue, Carbon $today): Carbon
    {
        $next = $currentDue->copy();

        if ($template->frequency === RecurringExpense::FREQ_WEEKLY) {
            $next->addWeek();
        } else {
            $day = $template->day_of_month ?? $currentDue->day;
            $next = $currentDue->copy()->addMonthNoOverflow()->day($day);
        }

        while ($next->lte($today)) {
            $next = $template->frequency === RecurringExpense::FREQ_WEEKLY
                ? $next->addWeek()
                : $next->addMonthNoOverflow();
        }

        return $next;
    }

    private function resolveVendorForTemplate(RecurringExpense $template): array
    {
        if ($template->vendor_id) {
            $vendor = Vendor::find($template->vendor_id);
            return [$vendor?->id, $vendor?->name];
        }

        $name = trim((string) $template->vendor_name);
        $phone = trim((string) $template->vendor_phone);
        if ($name === '' || $phone === '') {
            return [null, null];
        }

        $existing = Vendor::where('phone', $phone)->orderBy('id')->first();
        if ($existing) {
            return [$existing->id, $existing->name];
        }

        $vendor = Vendor::create([
            'name' => $name,
            'phone' => $phone,
            'contact_name' => $template->vendor_contact_name,
            'address' => $template->vendor_address,
            'is_active' => true,
        ]);

        return [$vendor->id, $vendor->name];
    }
}
