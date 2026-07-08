<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\EmployeeAllowance;
use App\Models\EmployeeAttendance;
use App\Models\EmployeeSalary;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollProrationTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);
    }

    private function createEmployee(array $overrides = []): Employee
    {
        static $counter = 1;

        return Employee::create(array_merge([
            'employee_number' => 'EMP-' . str_pad((string) $counter++, 4, '0', STR_PAD_LEFT),
            'company' => 'Micro Cool',
            'name' => 'Payroll Tester',
            'email' => null,
            'phone' => '777' . str_pad((string) random_int(1000, 9999), 4, '0', STR_PAD_LEFT),
            'type' => 'full-time',
            'position' => 'Technician',
            'hire_date' => '2025-01-01',
            'termination_date' => null,
            'status' => 'active',
            'basic_salary' => 10000,
            'work_status' => 'permanent',
            'passport_number' => 'P' . random_int(10000, 99999),
        ], $overrides));
    }

    private function addMonthlyAllowance(Employee $employee, float $amount, array $overrides = []): EmployeeAllowance
    {
        return $employee->allowances()->create(array_merge([
            'allowance_type' => 'food',
            'amount' => $amount,
            'frequency' => 'monthly',
            'start_date' => '2025-01-01',
            'end_date' => null,
            'is_active' => true,
        ], $overrides));
    }

    private function runPayroll(Employee $employee, int $year, int $month, array $payloadOverrides = []): EmployeeSalary
    {
        $user = $this->createAdmin();

        $payload = array_merge([
            'year' => $year,
            'month' => $month,
            'employee_ids' => [$employee->id],
        ], $payloadOverrides);

        $this->actingAs($user)
            ->post(route('payroll.store'), $payload)
            ->assertRedirect(route('payroll.index', ['year' => $year, 'month' => $month]));

        return EmployeeSalary::where('employee_id', $employee->id)
            ->where('year', $year)
            ->where('month', $month)
            ->firstOrFail();
    }

    public function test_mid_month_joining_uses_calendar_day_proration_for_basic_salary_and_fixed_allowance(): void
    {
        $employee = $this->createEmployee([
            'hire_date' => '2025-06-13',
            'basic_salary' => 10000,
        ]);

        $this->addMonthlyAllowance($employee, 1500, [
            'start_date' => '2025-06-01',
        ]);

        $payroll = $this->runPayroll($employee, 2025, 6);

        $this->assertSame('6000.00', $payroll->basic_salary);
        $this->assertSame('900.00', $payroll->allowances);
        $this->assertSame('6900.00', $payroll->gross_salary);
        $this->assertSame(18, $payroll->working_days);
        $this->assertSame('0.00', $payroll->absent_deduction);
    }

    public function test_joining_on_first_day_gets_full_month_salary_and_allowance(): void
    {
        $employee = $this->createEmployee([
            'hire_date' => '2025-06-01',
            'basic_salary' => 10000,
        ]);

        $this->addMonthlyAllowance($employee, 1500, [
            'start_date' => '2025-06-01',
        ]);

        $payroll = $this->runPayroll($employee, 2025, 6);

        $this->assertSame('10000.00', $payroll->basic_salary);
        $this->assertSame('1500.00', $payroll->allowances);
        $this->assertSame('11500.00', $payroll->gross_salary);
        $this->assertSame(30, $payroll->working_days);
    }

    public function test_joining_on_final_day_of_month_counts_that_day_inclusively(): void
    {
        $employee = $this->createEmployee([
            'hire_date' => '2025-06-30',
            'basic_salary' => 10000,
        ]);

        $this->addMonthlyAllowance($employee, 1500, [
            'start_date' => '2025-06-01',
        ]);

        $payroll = $this->runPayroll($employee, 2025, 6);

        $this->assertSame('333.33', $payroll->basic_salary);
        $this->assertSame('50.00', $payroll->allowances);
        $this->assertSame('383.33', $payroll->gross_salary);
        $this->assertSame(1, $payroll->working_days);
    }

    public function test_february_in_normal_year_uses_28_calendar_days(): void
    {
        $employee = $this->createEmployee([
            'hire_date' => '2025-02-14',
            'basic_salary' => 10000,
        ]);

        $this->addMonthlyAllowance($employee, 1500, [
            'start_date' => '2025-02-01',
        ]);

        $payroll = $this->runPayroll($employee, 2025, 2);

        $this->assertSame('5357.14', $payroll->basic_salary);
        $this->assertSame('803.57', $payroll->allowances);
        $this->assertSame('6160.71', $payroll->gross_salary);
        $this->assertSame(15, $payroll->working_days);
    }

    public function test_february_in_leap_year_uses_29_calendar_days(): void
    {
        $employee = $this->createEmployee([
            'hire_date' => '2024-02-14',
            'basic_salary' => 10000,
        ]);

        $this->addMonthlyAllowance($employee, 1500, [
            'start_date' => '2024-02-01',
        ]);

        $payroll = $this->runPayroll($employee, 2024, 2);

        $this->assertSame('5517.24', $payroll->basic_salary);
        $this->assertSame('827.59', $payroll->allowances);
        $this->assertSame('6344.83', $payroll->gross_salary);
        $this->assertSame(16, $payroll->working_days);
    }

    public function test_employee_leaving_mid_month_is_prorated_through_termination_date_inclusively(): void
    {
        $employee = $this->createEmployee([
            'hire_date' => '2025-01-01',
            'termination_date' => '2025-06-20',
            'status' => 'terminated',
            'basic_salary' => 10000,
        ]);

        $this->addMonthlyAllowance($employee, 1500, [
            'start_date' => '2025-01-01',
        ]);

        $payroll = $this->runPayroll($employee, 2025, 6);

        $this->assertSame('6666.67', $payroll->basic_salary);
        $this->assertSame('1000.00', $payroll->allowances);
        $this->assertSame('7666.67', $payroll->gross_salary);
        $this->assertSame(20, $payroll->working_days);
    }

    public function test_unpaid_leave_deduction_remains_separate_from_joining_proration(): void
    {
        $employee = $this->createEmployee([
            'hire_date' => '2025-06-13',
            'basic_salary' => 10000,
        ]);

        $this->addMonthlyAllowance($employee, 1500, [
            'start_date' => '2025-06-01',
        ]);

        EmployeeAttendance::create([
            'employee_id' => $employee->id,
            'date' => '2025-06-16',
            'status' => 'absent',
            'absence_reason' => 'unpaid_leave',
        ]);

        $payroll = $this->runPayroll($employee, 2025, 6);

        $this->assertSame('6000.00', $payroll->basic_salary);
        $this->assertSame('900.00', $payroll->allowances);
        $this->assertSame('333.33', $payroll->absent_deduction);
        $this->assertSame('6566.67', $payroll->net_salary);
    }
}
