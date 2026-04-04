<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DailyBudgetResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'daily_allowance' => $this->dailyAllowance,
            'left_today' => $this->leftToday,
            'spent_today' => $this->spentToday,
            'accumulated' => $this->accumulated,
            'monthly_income' => $this->monthlyIncome,
            'fixed_expenses' => $this->fixedExpenses,
            'variable_expenses' => $this->variableExpenses,
            'available_month' => $this->availableMonth,
            'days_left' => $this->daysLeft,
            'day_of_month' => $this->dayOfMonth,
            'days_in_month' => $this->daysInMonth,
        ];
    }
}
