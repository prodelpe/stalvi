<?php

namespace Database\Seeders;

use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(DatabaseSeeder::class);

        $current = Account::where('name', 'Current')->firstOrFail();
        $savings = Account::where('name', 'Savings')->firstOrFail();
        $categories = Category::leaves()->get()->keyBy('name');
        $now = CarbonImmutable::now();
        $startMonth = $now->subMonths(24)->startOfMonth();

        $cursor = $startMonth;
        while ($cursor->lte($now->startOfMonth())) {
            $monthsElapsed = $startMonth->diffInMonths($cursor);
            $salary = $monthsElapsed >= 12 ? 1650.00 : 1500.00;

            $this->seedMonth(
                $cursor,
                $current,
                $savings,
                $categories,
                $cursor->month === $now->month && $cursor->year === $now->year,
                $salary,
            );

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
        float $salary,
    ): void {
        $monthStart = $month->startOfMonth();
        $monthEnd = $isCurrentMonth ? CarbonImmutable::now() : $month->endOfMonth();
        $monthNumber = $month->month;

        // Income
        $this->createTransaction($current, $categories['Salary'], TransactionType::Income, $salary, $monthStart, 'Monthly salary');

        if (fake()->boolean(60) && ! $isCurrentMonth) {
            $this->createTransaction(
                $current,
                fake()->boolean(50) ? $categories['Wallapop'] : $categories['Vinted'],
                TransactionType::Income,
                fake()->randomFloat(2, 15, 80),
                $this->randomDate($monthStart, $monthEnd),
                fake()->randomElement(['Old jacket', 'Phone case', 'Books', 'Shoes', 'Lamp', 'Board game', 'Headphones', 'Backpack']),
            );
        }

        // Fixed expenses
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

        // Electricity (higher in winter)
        $elecDay = $monthStart->day(min(7, $monthStart->daysInMonth));
        if ($elecDay->lte($monthEnd)) {
            $isWinter = in_array($monthNumber, [12, 1, 2, 3]);
            $elecAmount = $isWinter ? fake()->randomFloat(2, 60, 85) : fake()->randomFloat(2, 28, 45);
            $this->createTransaction($current, $categories['Electricity'], TransactionType::Expense, $elecAmount, $elecDay, 'Electricity bill');
        }

        // Gas (only cold months)
        if (in_array($monthNumber, [10, 11, 12, 1, 2, 3])) {
            $gasDay = $monthStart->day(min(15, $monthStart->daysInMonth));
            if ($gasDay->lte($monthEnd)) {
                $this->createTransaction($current, $categories['Gas'], TransactionType::Expense, fake()->randomFloat(2, 20, 35), $gasDay, 'Gas bill');
            }
        }

        // Variable expenses
        $daysElapsed = $monthStart->diffInDays($monthEnd) + 1;
        $ratio = $daysElapsed / $monthStart->daysInMonth;

        $superTrips = (int) round(8 * $ratio);
        for ($i = 0; $i < $superTrips; $i++) {
            $this->createTransaction($current, $categories['Supermarket'], TransactionType::Expense, fake()->randomFloat(2, 28, 58), $this->randomDate($monthStart, $monthEnd));
        }

        $coffeeCount = (int) round(20 * $ratio);
        for ($i = 0; $i < $coffeeCount; $i++) {
            $this->createTransaction($current, $categories['Coffee'], TransactionType::Expense, fake()->randomFloat(2, 1.60, 1.80), $this->randomDate($monthStart, $monthEnd), 'Coffee');
        }

        $restaurantTrips = (int) round(fake()->numberBetween(1, 3) * $ratio);
        for ($i = 0; $i < $restaurantTrips; $i++) {
            $this->createTransaction($current, $categories['Restaurant'], TransactionType::Expense, fake()->randomFloat(2, 12, 32), $this->randomDate($monthStart, $monthEnd));
        }

        $fuelTimes = (int) round(fake()->numberBetween(2, 3) * $ratio);
        for ($i = 0; $i < $fuelTimes; $i++) {
            $this->createTransaction($current, $categories['Fuel'], TransactionType::Expense, fake()->randomFloat(2, 42, 62), $this->randomDate($monthStart, $monthEnd), 'Fuel');
        }

        $parkingTimes = max(0, (int) round(fake()->numberBetween(1, 2) * $ratio));
        for ($i = 0; $i < $parkingTimes; $i++) {
            $this->createTransaction($current, $categories['Parking'], TransactionType::Expense, fake()->randomFloat(2, 1.50, 4.00), $this->randomDate($monthStart, $monthEnd), 'Parking');
        }

        if ($ratio > 0.3 && fake()->boolean(40)) {
            $this->createTransaction($current, $categories['Cinema'], TransactionType::Expense, fake()->randomFloat(2, 8, 14), $this->randomDate($monthStart, $monthEnd));
        }

        if ($ratio > 0.3 && fake()->boolean(30)) {
            $this->createTransaction($current, $categories['Pharmacy'], TransactionType::Expense, fake()->randomFloat(2, 5, 20), $this->randomDate($monthStart, $monthEnd));
        }

        if ($ratio > 0.5 && fake()->boolean(20)) {
            $this->createTransaction($current, $categories['Clothing'], TransactionType::Expense, fake()->randomFloat(2, 20, 90), $this->randomDate($monthStart, $monthEnd));
        }

        if ($ratio > 0.5 && fake()->boolean(12)) {
            $this->createTransaction($current, $categories['Gifts'], TransactionType::Expense, fake()->randomFloat(2, 15, 50), $this->randomDate($monthStart, $monthEnd));
        }

        if (fake()->boolean(8)) {
            $this->createTransaction(
                $current,
                $categories['Music'],
                TransactionType::Expense,
                fake()->randomFloat(2, 20, 60),
                $this->randomDate($monthStart, $monthEnd),
                fake()->randomElement(['Concert', 'Festival ticket', 'Music event']),
            );
        }

        // Summer holiday travel (July / August)
        if (in_array($monthNumber, [7, 8]) && $ratio > 0.5 && fake()->boolean(70)) {
            $this->createTransaction(
                $current,
                $categories['Travel'],
                TransactionType::Expense,
                fake()->randomFloat(2, 150, 650),
                $this->randomDate($monthStart, $monthEnd),
                fake()->randomElement(['Flights', 'Hotel', 'Airbnb', 'Train tickets']),
            );
        }

        // Monthly transfer to savings
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
