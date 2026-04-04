<?php

namespace App\DTOs;

readonly class DailyBudgetResult
{
    public float $availableMonth;

    public float $remaining;

    public float $dailyBudget;

    public function __construct(
        public float $monthlyIncome,
        public float $fixedExpenses,
        public float $variableExpenses,
        public int $daysLeft,
        public int $dayOfMonth,
        public int $daysInMonth,
    ) {
        $this->availableMonth = $this->monthlyIncome - $this->fixedExpenses;
        $this->remaining = $this->availableMonth - $this->variableExpenses;
        $this->dailyBudget = $this->daysLeft > 0
            ? round($this->remaining / $this->daysLeft, 2)
            : 0.0;
    }
}
