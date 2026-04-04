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
            'daily_budget' => $this->dailyBudget,
            'monthly_income' => $this->monthlyIncome,
            'fixed_expenses' => $this->fixedExpenses,
            'variable_expenses' => $this->variableExpenses,
            'available_month' => $this->availableMonth,
            'remaining' => $this->remaining,
            'days_left' => $this->daysLeft,
            'day_of_month' => $this->dayOfMonth,
            'days_in_month' => $this->daysInMonth,
        ];
    }
}
