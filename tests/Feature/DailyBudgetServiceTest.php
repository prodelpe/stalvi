<?php

use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use App\Services\DailyBudgetService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->budget = Budget::factory()->create(['monthly_income' => 2000.00]);

    $this->fixedCategory = Category::factory()->create(['is_fixed' => true]);
    $this->variableCategory = Category::factory()->create(['is_fixed' => false]);
});

it('calculates daily budget correctly', function () {
    $date = CarbonImmutable::parse('2026-04-15');

    Transaction::factory()->expense()->create([
        'category_id' => $this->fixedCategory->id,
        'amount' => 500,
        'date' => '2026-04-01',
    ]);

    Transaction::factory()->expense()->create([
        'category_id' => $this->variableCategory->id,
        'amount' => 300,
        'date' => '2026-04-10',
    ]);

    $result = app(DailyBudgetService::class)->calculate($date);

    expect($result)
        ->monthlyIncome->toBe(2000.0)
        ->fixedExpenses->toBe(500.0)
        ->variableExpenses->toBe(300.0)
        ->availableMonth->toBe(1500.0)
        ->remaining->toBe(1200.0)
        ->daysLeft->toBe(16)
        ->dailyBudget->toBe(75.0)
        ->dayOfMonth->toBe(15)
        ->daysInMonth->toBe(30);
});

it('returns full budget on first day of month with no expenses', function () {
    $date = CarbonImmutable::parse('2026-04-01');

    $result = app(DailyBudgetService::class)->calculate($date);

    expect($result)
        ->availableMonth->toBe(2000.0)
        ->remaining->toBe(2000.0)
        ->daysLeft->toBe(30)
        ->dailyBudget->toBe(round(2000 / 30, 2));
});

it('returns remaining budget on last day of month', function () {
    $date = CarbonImmutable::parse('2026-04-30');

    Transaction::factory()->expense()->create([
        'category_id' => $this->variableCategory->id,
        'amount' => 1800,
        'date' => '2026-04-15',
    ]);

    $result = app(DailyBudgetService::class)->calculate($date);

    expect($result)
        ->daysLeft->toBe(1)
        ->remaining->toBe(200.0)
        ->dailyBudget->toBe(200.0);
});

it('returns negative daily budget when over budget', function () {
    $date = CarbonImmutable::parse('2026-04-20');

    Transaction::factory()->expense()->create([
        'category_id' => $this->variableCategory->id,
        'amount' => 2500,
        'date' => '2026-04-10',
    ]);

    $result = app(DailyBudgetService::class)->calculate($date);

    expect($result)
        ->remaining->toBeLessThan(0)
        ->dailyBudget->toBeLessThan(0);
});

it('returns null when no budget exists', function () {
    $this->budget->delete();

    $result = app(DailyBudgetService::class)->calculate();

    expect($result)->toBeNull();
});

it('ignores fixed expenses in variable calculation', function () {
    $date = CarbonImmutable::parse('2026-04-15');

    Transaction::factory()->expense()->create([
        'category_id' => $this->fixedCategory->id,
        'amount' => 800,
        'date' => '2026-04-05',
    ]);

    $result = app(DailyBudgetService::class)->calculate($date);

    expect($result)
        ->fixedExpenses->toBe(800.0)
        ->variableExpenses->toBe(0.0)
        ->availableMonth->toBe(1200.0)
        ->remaining->toBe(1200.0);
});
