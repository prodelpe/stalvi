<?php

namespace App\Services;

use App\DTOs\DailyBudgetResult;
use App\Models\Budget;
use App\Models\Transaction;
use Carbon\CarbonImmutable;

class DailyBudgetService
{
    public function calculate(?CarbonImmutable $date = null): ?DailyBudgetResult
    {
        $budget = Budget::first();

        if (! $budget) {
            return null;
        }

        $date ??= CarbonImmutable::now();
        $monthStart = $date->startOfMonth();
        $monthEnd = $date->endOfMonth();

        $fixedExpenses = (float) Transaction::expenses()
            ->whereHas('category', fn ($q) => $q->where('is_fixed', true))
            ->whereBetween('date', [$monthStart, $monthEnd])
            ->sum('amount');

        $variableExpenses = (float) Transaction::expenses()
            ->whereHas('category', fn ($q) => $q->where('is_fixed', false))
            ->whereBetween('date', [$monthStart, $date])
            ->sum('amount');

        $daysLeft = $date->diffInDays($monthEnd) + 1;

        return new DailyBudgetResult(
            monthlyIncome: (float) $budget->monthly_income,
            fixedExpenses: $fixedExpenses,
            variableExpenses: $variableExpenses,
            daysLeft: $daysLeft,
            dayOfMonth: $date->day,
            daysInMonth: $date->daysInMonth,
        );
    }
}
