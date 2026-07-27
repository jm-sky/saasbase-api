<?php

namespace App\Domain\Financial\Controllers;

use App\Domain\Auth\Models\User;
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
        /** @var User $user */
        $user = Auth::user();
        $tenantId = $user->getTenantId();

        $now = Carbon::now();
        $currentMonth = $now->copy()->startOfMonth();
        $previousMonth = $now->copy()->subMonth()->startOfMonth();
        $currentYear = $now->copy()->startOfYear();
        $previousYear = $now->copy()->subYear()->startOfYear();

        // Current month balance
        $currentMonthRevenue = $this->getRevenueForPeriod($tenantId, $currentMonth, $currentMonth->copy()->endOfMonth());
        $currentMonthExpenses = $this->getExpensesForPeriod($tenantId, $currentMonth, $currentMonth->copy()->endOfMonth());
        $currentMonthBalance = $currentMonthRevenue - $currentMonthExpenses;

        // Previous month balance
        $previousMonthRevenue = $this->getRevenueForPeriod($tenantId, $previousMonth, $previousMonth->copy()->endOfMonth());
        $previousMonthExpenses = $this->getExpensesForPeriod($tenantId, $previousMonth, $previousMonth->copy()->endOfMonth());
        $previousMonthBalance = $previousMonthRevenue - $previousMonthExpenses;

        // Current year balance
        $currentYearRevenue = $this->getRevenueForPeriod($tenantId, $currentYear, $currentYear->copy()->endOfYear());
        $currentYearExpenses = $this->getExpensesForPeriod($tenantId, $currentYear, $currentYear->copy()->endOfYear());
        $currentYearBalance = $currentYearRevenue - $currentYearExpenses;

        // Previous year balance
        $previousYearRevenue = $this->getRevenueForPeriod($tenantId, $previousYear, $previousYear->copy()->endOfYear());
        $previousYearExpenses = $this->getExpensesForPeriod($tenantId, $previousYear, $previousYear->copy()->endOfYear());
        $previousYearBalance = $previousYearRevenue - $previousYearExpenses;

        $data = [
            'month' => [
                'current' => $currentMonthBalance,
                'previous' => $previousMonthBalance,
                'year' => $now->year,
                'changePercent' => $this->calculatePercentageChange($previousMonthBalance, $currentMonthBalance),
            ],
            'year' => [
                'current' => $currentYearBalance,
                'previous' => $previousYearBalance,
                'year' => $now->year,
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
        /** @var User $user */
        $user = Auth::user();
        $tenantId = $user->getTenantId();

        $now = Carbon::now();
        $currentMonth = $now->copy()->startOfMonth();
        $previousMonth = $now->copy()->subMonth()->startOfMonth();
        $currentYear = $now->copy()->startOfYear();
        $previousYear = $now->copy()->subYear()->startOfYear();

        // Current month revenue
        $currentMonthRevenue = $this->getRevenueForPeriod($tenantId, $currentMonth, $currentMonth->copy()->endOfMonth());

        // Previous month revenue
        $previousMonthRevenue = $this->getRevenueForPeriod($tenantId, $previousMonth, $previousMonth->copy()->endOfMonth());

        // Current year revenue
        $currentYearRevenue = $this->getRevenueForPeriod($tenantId, $currentYear, $currentYear->copy()->endOfYear());

        // Previous year revenue
        $previousYearRevenue = $this->getRevenueForPeriod($tenantId, $previousYear, $previousYear->copy()->endOfYear());

        $data = [
            'month' => [
                'current' => $currentMonthRevenue,
                'previous' => $previousMonthRevenue,
                'year' => $now->year,
                'changePercent' => $this->calculatePercentageChange($previousMonthRevenue, $currentMonthRevenue),
            ],
            'year' => [
                'current' => $currentYearRevenue,
                'previous' => $previousYearRevenue,
                'year' => $now->year,
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
        /** @var User $user */
        $user = Auth::user();
        $tenantId = $user->getTenantId();

        $now = Carbon::now();
        $currentMonth = $now->copy()->startOfMonth();
        $previousMonth = $now->copy()->subMonth()->startOfMonth();
        $currentYear = $now->copy()->startOfYear();
        $previousYear = $now->copy()->subYear()->startOfYear();

        // Current month expenses
        $currentMonthExpenses = $this->getExpensesForPeriod($tenantId, $currentMonth, $currentMonth->copy()->endOfMonth());

        // Previous month expenses
        $previousMonthExpenses = $this->getExpensesForPeriod($tenantId, $previousMonth, $previousMonth->copy()->endOfMonth());

        // Current year expenses
        $currentYearExpenses = $this->getExpensesForPeriod($tenantId, $currentYear, $currentYear->copy()->endOfYear());

        // Previous year expenses
        $previousYearExpenses = $this->getExpensesForPeriod($tenantId, $previousYear, $previousYear->copy()->endOfYear());

        $data = [
            'month' => [
                'current' => $currentMonthExpenses,
                'previous' => $previousMonthExpenses,
                'year' => $now->year,
                'changePercent' => $this->calculatePercentageChange($previousMonthExpenses, $currentMonthExpenses),
            ],
            'year' => [
                'current' => $currentYearExpenses,
                'previous' => $previousYearExpenses,
                'year' => $now->year,
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
        /** @var User $user */
        $user = Auth::user();
        $tenantId = $user->getTenantId();

        $currentYear = Carbon::now()->startOfYear();
        $yearStart = $currentYear->copy()->startOfYear();
        $yearEnd = $currentYear->copy()->endOfYear();

        // Two grouped queries instead of 24 period scans (Sentry SAASBASE-API-38).
        $revenueByMonth = Invoice::query()
            ->where('tenant_id', $tenantId)
            ->whereIn('status', [InvoiceStatus::ISSUED, InvoiceStatus::COMPLETED])
            ->whereBetween('issue_date', [$yearStart, $yearEnd])
            ->selectRaw('EXTRACT(MONTH FROM issue_date)::int as month, COALESCE(SUM(total_gross), 0) as total')
            ->groupByRaw('EXTRACT(MONTH FROM issue_date)')
            ->pluck('total', 'month');

        $expensesByMonth = Expense::query()
            ->where('tenant_id', $tenantId)
            ->where('status', '!=', InvoiceStatus::CANCELLED)
            ->whereBetween('issue_date', [$yearStart, $yearEnd])
            ->selectRaw('EXTRACT(MONTH FROM issue_date)::int as month, COALESCE(SUM(total_gross), 0) as total')
            ->groupByRaw('EXTRACT(MONTH FROM issue_date)')
            ->pluck('total', 'month');

        $monthsData = [];

        for ($month = 1; $month <= 12; $month++) {
            $revenue = (float) ($revenueByMonth[$month] ?? 0);
            $expenses = (float) ($expensesByMonth[$month] ?? 0);

            $monthsData[] = [
                'month' => $month,
                'monthName' => $currentYear->copy()->month($month)->format('M'),
                'revenue' => $revenue,
                'expenses' => $expenses,
                'balance' => $revenue - $expenses,
            ];
        }

        $data = [
            'year' => $currentYear->year,
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
        return (float) Invoice::query()
            ->where('tenant_id', $tenantId)
            ->whereIn('status', [InvoiceStatus::ISSUED, InvoiceStatus::COMPLETED])
            ->whereBetween('issue_date', [$startDate, $endDate])
            ->sum('total_gross');
    }

    /**
     * Get expenses for a specific period from expenses.
     * Only includes expenses that are not cancelled.
     */
    private function getExpensesForPeriod(string $tenantId, Carbon $startDate, Carbon $endDate): float
    {
        return (float) Expense::query()
            ->where('tenant_id', $tenantId)
            ->where('status', '!=', InvoiceStatus::CANCELLED)
            ->whereBetween('issue_date', [$startDate, $endDate])
            ->sum('total_gross');
    }

    /**
     * Calculate percentage change between two values.
     */
    private function calculatePercentageChange(float $previousValue, float $currentValue): float
    {
        if ($previousValue == 0) {
            return $currentValue > 0 ? 100.0 : 0.0;
        }

        return round((($currentValue - $previousValue) / abs($previousValue)) * 100, 2);
    }
}
