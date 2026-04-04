<?php

namespace Database\Seeders;

use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;

class DevelopmentSeeder extends Seeder
{
    /**
     * Seed realistic transactions for local development.
     * Covers the last 6 full months + current month up to today.
     */
    public function run(): void
    {
        if (! app()->isLocal()) {
            return;
        }

        $current = Account::where('name', 'Current')->first();
        $savings = Account::where('name', 'Savings')->first();

        $categories = Category::leaves()->get()->keyBy('name');

        $now = CarbonImmutable::now();
        $startMonth = $now->subMonths(6)->startOfMonth();

        $cursor = $startMonth;
        while ($cursor->lte($now->startOfMonth())) {
            $this->seedMonth($cursor, $current, $savings, $categories, $cursor->month === $now->month && $cursor->year === $now->year);
            $cursor = $cursor->addMonth();
        }
    }

    /**
     * @param  Collection<int, Category>  $categories
     */
    private function seedMonth(
        CarbonImmutable $month,
        Account $current,
        Account $savings,
        $categories,
        bool $isCurrentMonth,
    ): void {
        $monthStart = $month->startOfMonth();
        $monthEnd = $isCurrentMonth ? CarbonImmutable::now() : $month->endOfMonth();

        // === INCOME ===

        // Salary: 1500 EUR on the 1st
        if ($monthStart->lte($monthEnd)) {
            $this->createTransaction($current, $categories['Salary'], TransactionType::Income, 1500.00, $monthStart, 'Monthly salary');
        }

        // Occasional Wallapop/Vinted sale (60% chance per month)
        if (fake()->boolean(60) && ! $isCurrentMonth) {
            $this->createTransaction(
                $current,
                fake()->boolean(50) ? $categories['Wallapop'] : $categories['Vinted'],
                TransactionType::Income,
                fake()->randomFloat(2, 15, 80),
                $this->randomDate($monthStart, $monthEnd),
                fake()->randomElement(['Old jacket', 'Phone case', 'Books', 'Shoes', 'Lamp', 'Board game']),
            );
        }

        // === FIXED MONTHLY EXPENSES ===

        $fixedExpenses = [
            ['category' => 'Community', 'amount' => 210.00, 'day' => 1, 'desc' => 'Community fees'],
            ['category' => 'Credit', 'amount' => 273.00, 'day' => 1, 'desc' => 'Monthly credit payment'],
            ['category' => 'Gym', 'amount' => 59.00, 'day' => 1, 'desc' => 'Gym membership'],
            ['category' => 'Internet', 'amount' => 13.00, 'day' => 1, 'desc' => 'Internet'],
            ['category' => 'Subscriptions', 'amount' => 7.99, 'day' => 15, 'desc' => 'Spotify'],
            ['category' => 'Subscriptions', 'amount' => 1.99, 'day' => 15, 'desc' => 'iCloud'],
        ];

        foreach ($fixedExpenses as $expense) {
            $day = $monthStart->day(min($expense['day'], $monthStart->daysInMonth));
            if ($day->lte($monthEnd)) {
                $this->createTransaction($current, $categories[$expense['category']], TransactionType::Expense, $expense['amount'], $day, $expense['desc']);
            }
        }

        // Electricity: 30-40 EUR on the 7th
        $elecDay = $monthStart->day(min(7, $monthStart->daysInMonth));
        if ($elecDay->lte($monthEnd)) {
            $this->createTransaction($current, $categories['Electricity'], TransactionType::Expense, fake()->randomFloat(2, 30, 40), $elecDay, 'Electricity bill');
        }

        // Gas: 20-25 EUR on the 15th
        $gasDay = $monthStart->day(min(15, $monthStart->daysInMonth));
        if ($gasDay->lte($monthEnd)) {
            $this->createTransaction($current, $categories['Gas'], TransactionType::Expense, fake()->randomFloat(2, 20, 25), $gasDay, 'Gas bill');
        }

        // === VARIABLE EXPENSES ===
        // Scale by proportion of month elapsed (for current month)
        $daysElapsed = $monthStart->diffInDays($monthEnd) + 1;
        $daysInMonth = $monthStart->daysInMonth;
        $ratio = $daysElapsed / $daysInMonth;

        // Supermarket: ~8 per month (2/week), 30-50 EUR each
        $superTrips = (int) round(8 * $ratio);
        for ($t = 0; $t < $superTrips; $t++) {
            $this->createTransaction($current, $categories['Supermarket'], TransactionType::Expense, fake()->randomFloat(2, 30, 50), $this->randomDate($monthStart, $monthEnd));
        }

        // Coffee: ~24 per month (6/week) at 1.60 EUR
        $coffeeCount = (int) round(24 * $ratio);
        for ($c = 0; $c < $coffeeCount; $c++) {
            $this->createTransaction($current, $categories['Coffee'], TransactionType::Expense, 1.60, $this->randomDate($monthStart, $monthEnd), 'Coffee');
        }

        // Restaurant: 1-2 times per month, 15-25 EUR
        $restaurantTrips = (int) round(fake()->numberBetween(1, 2) * $ratio);
        for ($r = 0; $r < $restaurantTrips; $r++) {
            $this->createTransaction($current, $categories['Restaurant'], TransactionType::Expense, fake()->randomFloat(2, 15, 25), $this->randomDate($monthStart, $monthEnd));
        }

        // Fuel: 2-3 times per month, 40-55 EUR
        $fuelTimes = (int) round(fake()->numberBetween(2, 3) * $ratio);
        for ($f = 0; $f < $fuelTimes; $f++) {
            $this->createTransaction($current, $categories['Fuel'], TransactionType::Expense, fake()->randomFloat(2, 40, 55), $this->randomDate($monthStart, $monthEnd), 'Fuel');
        }

        // Cinema: 0-1 per month (only if enough days have passed)
        if ($ratio > 0.3 && fake()->boolean(40)) {
            $this->createTransaction($current, $categories['Cinema'], TransactionType::Expense, fake()->randomFloat(2, 8, 12), $this->randomDate($monthStart, $monthEnd));
        }

        // Pharmacy: 0-1 per month
        if ($ratio > 0.3 && fake()->boolean(30)) {
            $this->createTransaction($current, $categories['Pharmacy'], TransactionType::Expense, fake()->randomFloat(2, 5, 15), $this->randomDate($monthStart, $monthEnd));
        }

        // Clothing: 0-1 every 2-3 months
        if ($ratio > 0.5 && fake()->boolean(15)) {
            $this->createTransaction($current, $categories['Clothing'], TransactionType::Expense, fake()->randomFloat(2, 20, 50), $this->randomDate($monthStart, $monthEnd));
        }

        // Gifts: 0-1 every 3-4 months
        if ($ratio > 0.5 && fake()->boolean(10)) {
            $this->createTransaction($current, $categories['Gifts'], TransactionType::Expense, fake()->randomFloat(2, 15, 30), $this->randomDate($monthStart, $monthEnd));
        }

        // Parking: 1-2 per month
        $parkingTimes = max(0, (int) round(fake()->numberBetween(1, 2) * $ratio));
        for ($p = 0; $p < $parkingTimes; $p++) {
            $this->createTransaction($current, $categories['Parking'], TransactionType::Expense, fake()->randomFloat(2, 1, 3), $this->randomDate($monthStart, $monthEnd), 'Parking');
        }

        // Transfer to savings (simulate allocation: expense on current, income on savings)
        $transferDay = $monthStart->day(min(2, $monthStart->daysInMonth));
        if ($transferDay->lte($monthEnd)) {
            $this->createTransaction($current, $categories['To savings'], TransactionType::Expense, 500.00, $transferDay, 'Monthly transfer');
            $this->createTransaction($savings, $categories['To savings'], TransactionType::Income, 500.00, $transferDay, 'Monthly transfer');
        }
    }

    private function createTransaction(
        Account $account,
        Category $category,
        TransactionType $type,
        float $amount,
        CarbonImmutable $date,
        ?string $description = null,
    ): void {
        Transaction::create([
            'account_id' => $account->id,
            'category_id' => $category->id,
            'type' => $type,
            'amount' => $amount,
            'date' => $date,
            'description' => $description,
        ]);
    }

    private function randomDate(CarbonImmutable $start, CarbonImmutable $end): CarbonImmutable
    {
        return $start->addDays(fake()->numberBetween(0, max(0, $start->diffInDays($end))));
    }
}
