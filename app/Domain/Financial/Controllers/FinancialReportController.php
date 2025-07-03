<?php

namespace App\Domain\Financial\Controllers;

use App\Domain\Expense\Models\Expense;
use App\Domain\Financial\Enums\InvoiceStatus;
use App\Domain\Financial\Resources\FinancialBalanceWidgetResource;
use App\Domain\Financial\Resources\FinancialExpensesWidgetResource;
use App\Domain\Financial\Resources\FinancialOverviewWidgetResource;
use App\Domain\Financial\Resources\FinancialRevenueWidgetResource;
use App\Domain\Invoice\Models\Invoice;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FinancialReportController extends Controller
{
    /**
     * Get total balance widget data (Invoice + Expense models).
     *
     * Returns:
     * - Total balance for current month + % change from previous month
     * - Total balance for current year + % change from previous year
     */
    public function balanceWidget(Request $request): JsonResponse
    {
        /** @var \App\Domain\Auth\Models\User $user */
        $user     = Auth::user();
        $tenantId = $user->getTenantId();

        $now           = Carbon::now();
        $currentMonth  = $now->copy()->startOfMonth();
        $previousMonth = $now->copy()->subMonth()->startOfMonth();
        $currentYear   = $now->copy()->startOfYear();
        $previousYear  = $now->copy()->subYear()->startOfYear();

        // Current month balance
        $currentMonthRevenue  = $this->getRevenueForPeriod($tenantId, $currentMonth, $currentMonth->copy()->endOfMonth());
        $currentMonthExpenses = $this->getExpensesForPeriod($tenantId, $currentMonth, $currentMonth->copy()->endOfMonth());
        $currentMonthBalance  = $currentMonthRevenue - $currentMonthExpenses;

        // Previous month balance
        $previousMonthRevenue  = $this->getRevenueForPeriod($tenantId, $previousMonth, $previousMonth->copy()->endOfMonth());
        $previousMonthExpenses = $this->getExpensesForPeriod($tenantId, $previousMonth, $previousMonth->copy()->endOfMonth());
        $previousMonthBalance  = $previousMonthRevenue - $previousMonthExpenses;

        // Current year balance
        $currentYearRevenue  = $this->getRevenueForPeriod($tenantId, $currentYear, $currentYear->copy()->endOfYear());
        $currentYearExpenses = $this->getExpensesForPeriod($tenantId, $currentYear, $currentYear->copy()->endOfYear());
        $currentYearBalance  = $currentYearRevenue - $currentYearExpenses;

        // Previous year balance
        $previousYearRevenue  = $this->getRevenueForPeriod($tenantId, $previousYear, $previousYear->copy()->endOfYear());
        $previousYearExpenses = $this->getExpensesForPeriod($tenantId, $previousYear, $previousYear->copy()->endOfYear());
        $previousYearBalance  = $previousYearRevenue - $previousYearExpenses;

        $data = [
            'currentMonth' => [
                'balance'       => $currentMonthBalance,
                'changePercent' => $this->calculatePercentageChange($previousMonthBalance, $currentMonthBalance),
            ],
            'currentYear' => [
                'balance'       => $currentYearBalance,
                'changePercent' => $this->calculatePercentageChange($previousYearBalance, $currentYearBalance),
            ],
        ];

        return response()->json(['data' => new FinancialBalanceWidgetResource($data)]);
    }

    /**
     * Get total revenue widget data (Invoice model only).
     *
     * Returns:
     * - Total revenue for current month + % change from previous month
     * - Total revenue for current year + % change from previous year
     */
    public function revenueWidget(Request $request): JsonResponse
    {
        /** @var \App\Domain\Auth\Models\User $user */
        $user     = Auth::user();
        $tenantId = $user->getTenantId();

        $now           = Carbon::now();
        $currentMonth  = $now->copy()->startOfMonth();
        $previousMonth = $now->copy()->subMonth()->startOfMonth();
        $currentYear   = $now->copy()->startOfYear();
        $previousYear  = $now->copy()->subYear()->startOfYear();

        // Current month revenue
        $currentMonthRevenue = $this->getRevenueForPeriod($tenantId, $currentMonth, $currentMonth->copy()->endOfMonth());

        // Previous month revenue
        $previousMonthRevenue = $this->getRevenueForPeriod($tenantId, $previousMonth, $previousMonth->copy()->endOfMonth());

        // Current year revenue
        $currentYearRevenue = $this->getRevenueForPeriod($tenantId, $currentYear, $currentYear->copy()->endOfYear());

        // Previous year revenue
        $previousYearRevenue = $this->getRevenueForPeriod($tenantId, $previousYear, $previousYear->copy()->endOfYear());

        $data = [
            'currentMonth' => [
                'revenue'       => $currentMonthRevenue,
                'changePercent' => $this->calculatePercentageChange($previousMonthRevenue, $currentMonthRevenue),
            ],
            'currentYear' => [
                'revenue'       => $currentYearRevenue,
                'changePercent' => $this->calculatePercentageChange($previousYearRevenue, $currentYearRevenue),
            ],
        ];

        return response()->json(['data' => new FinancialRevenueWidgetResource($data)]);
    }

    /**
     * Get expenses widget data (Expense model only).
     *
     * Returns:
     * - Total expenses for current month + % change from previous month
     * - Total expenses for current year + % change from previous year
     */
    public function expensesWidget(Request $request): JsonResponse
    {
        /** @var \App\Domain\Auth\Models\User $user */
        $user     = Auth::user();
        $tenantId = $user->getTenantId();

        $now           = Carbon::now();
        $currentMonth  = $now->copy()->startOfMonth();
        $previousMonth = $now->copy()->subMonth()->startOfMonth();
        $currentYear   = $now->copy()->startOfYear();
        $previousYear  = $now->copy()->subYear()->startOfYear();

        // Current month expenses
        $currentMonthExpenses = $this->getExpensesForPeriod($tenantId, $currentMonth, $currentMonth->copy()->endOfMonth());

        // Previous month expenses
        $previousMonthExpenses = $this->getExpensesForPeriod($tenantId, $previousMonth, $previousMonth->copy()->endOfMonth());

        // Current year expenses
        $currentYearExpenses = $this->getExpensesForPeriod($tenantId, $currentYear, $currentYear->copy()->endOfYear());

        // Previous year expenses
        $previousYearExpenses = $this->getExpensesForPeriod($tenantId, $previousYear, $previousYear->copy()->endOfYear());

        $data = [
            'currentMonth' => [
                'expenses'      => $currentMonthExpenses,
                'changePercent' => $this->calculatePercentageChange($previousMonthExpenses, $currentMonthExpenses),
            ],
            'currentYear' => [
                'expenses'      => $currentYearExpenses,
                'changePercent' => $this->calculatePercentageChange($previousYearExpenses, $currentYearExpenses),
            ],
        ];

        return response()->json(['data' => new FinancialExpensesWidgetResource($data)]);
    }

    /**
     * Get overview widget data (Invoice + Expense models).
     *
     * Returns:
     * - Total revenue for all months in current year
     * - Total expenses for all months in current year
     * - Balance (revenue - expenses) for all months in current year
     */
    public function overviewWidget(Request $request): JsonResponse
    {
        /** @var \App\Domain\Auth\Models\User $user */
        $user     = Auth::user();
        $tenantId = $user->getTenantId();

        $currentYear = Carbon::now()->startOfYear();
        $monthsData  = [];

        for ($month = 1; $month <= 12; ++$month) {
            $startOfMonth = $currentYear->copy()->month($month)->startOfMonth();
            $endOfMonth   = $startOfMonth->copy()->endOfMonth();

            $revenue  = $this->getRevenueForPeriod($tenantId, $startOfMonth, $endOfMonth);
            $expenses = $this->getExpensesForPeriod($tenantId, $startOfMonth, $endOfMonth);
            $balance  = $revenue - $expenses;

            $monthsData[] = [
                'month'     => $month,
                'monthName' => $startOfMonth->format('M'),
                'revenue'   => $revenue,
                'expenses'  => $expenses,
                'balance'   => $balance,
            ];
        }

        $data = [
            'year'   => $currentYear->year,
            'months' => $monthsData,
        ];

        return response()->json(['data' => new FinancialOverviewWidgetResource($data)]);
    }

    /**
     * Get revenue for a specific period from invoices.
     * Only includes completed/issued invoices.
     */
    private function getRevenueForPeriod(string $tenantId, Carbon $startDate, Carbon $endDate): float
    {
        $result = Invoice::where('tenant_id', $tenantId)
            ->whereIn('general_status', [InvoiceStatus::ISSUED, InvoiceStatus::COMPLETED])
            ->whereBetween('issue_date', [$startDate, $endDate])
            ->get()
            ->sum(function ($invoice) {
                return $invoice->total_gross->toFloat();
            })
        ;

        return (float) $result;
    }

    /**
     * Get expenses for a specific period from expenses.
     * Only includes expenses that are not cancelled.
     */
    private function getExpensesForPeriod(string $tenantId, Carbon $startDate, Carbon $endDate): float
    {
        $result = Expense::where('tenant_id', $tenantId)
            ->where('general_status', '!=', InvoiceStatus::CANCELLED)
            ->whereBetween('issue_date', [$startDate, $endDate])
            ->get()
            ->sum(function ($expense) {
                return $expense->total_gross->toFloat();
            })
        ;

        return (float) $result;
    }

    /**
     * Calculate percentage change between two values.
     */
    private function calculatePercentageChange(float $previousValue, float $currentValue): float
    {
        if (0 == $previousValue) {
            return $currentValue > 0 ? 100.0 : 0.0;
        }

        return round((($currentValue - $previousValue) / abs($previousValue)) * 100, 2);
    }
}
