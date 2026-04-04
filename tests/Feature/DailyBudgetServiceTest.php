<?php

use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use App\Services\DailyBudgetService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->budget = Budget::factory()->create(['monthly_income' => 3000.00]);

    $this->fixedCategory = Category::factory()->create(['is_fixed' => true]);
    $this->variableCategory = Category::factory()->create(['is_fixed' => false]);
});

it('calculates daily allowance correctly', function () {
    $date = CarbonImmutable::parse('2026-04-01');

    $result = app(DailyBudgetService::class)->calculate($date);

    // 3000 income, 0 fixed = 3000 available / 30 days = 100/day
    expect($result)
        ->monthlyIncome->toBe(3000.0)
        ->dailyAllowance->toBe(100.0)
        ->leftToday->toBe(100.0)
        ->spentToday->toBe(0.0)
        ->accumulated->toBe(0.0);
});

it('subtracts spent today from left today', function () {
    $date = CarbonImmutable::parse('2026-04-01');

    Transaction::factory()->expense()->create([
        'category_id' => $this->variableCategory->id,
        'amount' => 30,
        'date' => '2026-04-01',
    ]);

    $result = app(DailyBudgetService::class)->calculate($date);

    // 100/day - 30 spent = 70 left
    expect($result)
        ->dailyAllowance->toBe(100.0)
        ->spentToday->toBe(30.0)
        ->leftToday->toBe(70.0);
});

it('accumulates unspent budget from previous days', function () {
    $date = CarbonImmutable::parse('2026-04-03');

    // Days 1 and 2: no spending. Accumulated = 100*2 - 0 = 200
    $result = app(DailyBudgetService::class)->calculate($date);

    expect($result)
        ->dailyAllowance->toBe(100.0)
        ->accumulated->toBe(200.0)
        ->leftToday->toBe(300.0); // 100 + 200
});

it('reduces accumulated when overspending previous days', function () {
    $date = CarbonImmutable::parse('2026-04-03');

    // Day 1: spent 150 (50 over allowance)
    Transaction::factory()->expense()->create([
        'category_id' => $this->variableCategory->id,
        'amount' => 150,
        'date' => '2026-04-01',
    ]);

    // Day 2: spent 80 (20 under)
    Transaction::factory()->expense()->create([
        'category_id' => $this->variableCategory->id,
        'amount' => 80,
        'date' => '2026-04-02',
    ]);

    $result = app(DailyBudgetService::class)->calculate($date);

    // Expected for 2 days: 200. Spent before today: 230. Accumulated: -30
    expect($result)
        ->accumulated->toBe(-30.0)
        ->leftToday->toBe(70.0); // 100 + (-30) - 0
});

it('returns null when no budget exists', function () {
    $this->budget->delete();

    $result = app(DailyBudgetService::class)->calculate();

    expect($result)->toBeNull();
});

it('ignores fixed expenses in daily calculation', function () {
    $date = CarbonImmutable::parse('2026-04-01');

    Transaction::factory()->expense()->create([
        'category_id' => $this->fixedCategory->id,
        'amount' => 500,
        'date' => '2026-04-01',
    ]);

    $result = app(DailyBudgetService::class)->calculate($date);

    // 3000 - 500 fixed = 2500 / 30 = 83.33/day
    expect($result)
        ->fixedExpenses->toBe(500.0)
        ->dailyAllowance->toBe(83.33)
        ->spentToday->toBe(0.0)
        ->leftToday->toBe(83.33);
});
