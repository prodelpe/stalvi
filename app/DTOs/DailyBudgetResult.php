<?php

namespace App\DTOs;

readonly class DailyBudgetResult
{
    public float $availableMonth;

    public float $dailyAllowance;

    public float $accumulated;

    public float $leftToday;

    public function __construct(
        public float $monthlyIncome,
        public float $fixedExpenses,
        public float $variableExpenses,
        public float $spentToday,
        public int $daysLeft,
        public int $dayOfMonth,
        public int $daysInMonth,
    ) {
        $this->availableMonth = $this->monthlyIncome - $this->fixedExpenses;
        $this->dailyAllowance = $this->daysInMonth > 0
            ? round($this->availableMonth / $this->daysInMonth, 2)
            : 0.0;

        $daysElapsed = $this->dayOfMonth - 1;
        $expectedSpent = $this->dailyAllowance * $daysElapsed;
        $spentBeforeToday = $this->variableExpenses - $this->spentToday;
        $this->accumulated = round($expectedSpent - $spentBeforeToday, 2);

        $this->leftToday = round($this->dailyAllowance + $this->accumulated - $this->spentToday, 2);
    }
}
