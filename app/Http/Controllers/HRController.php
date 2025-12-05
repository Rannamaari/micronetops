<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeLoan;
use App\Models\EmployeeSalary;
use Illuminate\Http\Request;

class HRController extends Controller
{
    /**
     * Display the HR dashboard
     */
    public function dashboard()
    {
        // Get HR statistics
        $totalEmployees = Employee::count();
        $activeEmployees = Employee::where('status', 'active')->count();
        $inactiveEmployees = Employee::where('status', 'inactive')->count();
        $terminatedEmployees = Employee::where('status', 'terminated')->count();

        // Employee breakdown by type
        $fullTimeEmployees = Employee::where('type', 'full-time')->where('status', 'active')->count();
        $partTimeEmployees = Employee::where('type', 'part-time')->where('status', 'active')->count();
        $contractEmployees = Employee::where('type', 'contract')->where('status', 'active')->count();

        // Loans statistics
        $activeLoans = EmployeeLoan::where('status', 'active')->count();
        $totalLoanAmount = EmployeeLoan::where('status', 'active')->sum('amount');
        $totalRemainingBalance = EmployeeLoan::where('status', 'active')->sum('remaining_balance');

        // Payroll statistics (current month)
        $currentMonth = now()->month;
        $currentYear = now()->year;
        $currentMonthPayrolls = EmployeeSalary::where('month', $currentMonth)
            ->where('year', $currentYear)
            ->count();
        $currentMonthTotalPayout = EmployeeSalary::where('month', $currentMonth)
            ->where('year', $currentYear)
            ->sum('net_salary');

        // Recent employees
        $recentEmployees = Employee::latest()->take(5)->get();

        // Employees with expiring documents (next 30 days)
        $expiringDocuments = Employee::expiringDocuments(30)->count();

        return view('hr.dashboard', compact(
            'totalEmployees',
            'activeEmployees',
            'inactiveEmployees',
            'terminatedEmployees',
            'fullTimeEmployees',
            'partTimeEmployees',
            'contractEmployees',
            'activeLoans',
            'totalLoanAmount',
            'totalRemainingBalance',
            'currentMonthPayrolls',
            'currentMonthTotalPayout',
            'recentEmployees',
            'expiringDocuments'
        ));
    }
}
