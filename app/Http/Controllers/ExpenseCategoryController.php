<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategory;
use Illuminate\Http\Request;

class ExpenseCategoryController extends Controller
{
    public function index()
    {
        $categories = ExpenseCategory::orderBy('name')->paginate(25);

        return view('expenses.categories.index', compact('categories'));
    }

    public function create()
    {
        $types = ExpenseCategory::getTypes();

        return view('expenses.categories.create', compact('types'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:expense_categories,name'],
            'type' => ['required', 'in:' . implode(',', array_keys(ExpenseCategory::getTypes()))],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = (bool) $request->input('is_active', 1);

        ExpenseCategory::create($validated);

        return redirect()->route('expense-categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function edit(ExpenseCategory $expenseCategory)
    {
        $types = ExpenseCategory::getTypes();

        return view('expenses.categories.edit', compact('expenseCategory', 'types'));
    }

    public function update(Request $request, ExpenseCategory $expenseCategory)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:expense_categories,name,' . $expenseCategory->id],
            'type' => ['required', 'in:' . implode(',', array_keys(ExpenseCategory::getTypes()))],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = (bool) $request->input('is_active', 1);

        $expenseCategory->update($validated);

        return redirect()->route('expense-categories.index')
            ->with('success', 'Category updated successfully.');
    }
}
