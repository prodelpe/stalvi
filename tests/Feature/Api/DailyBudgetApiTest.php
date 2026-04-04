<?php

use App\Models\Budget;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['api.token' => 'test-token']);
    $this->headers = ['Authorization' => 'Bearer test-token'];
});

it('returns daily budget', function () {
    Budget::factory()->create(['monthly_income' => 2000]);

    $this->getJson('/api/daily-budget', $this->headers)
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                'daily_allowance',
                'left_today',
                'spent_today',
                'accumulated',
                'monthly_income',
                'fixed_expenses',
                'variable_expenses',
                'available_month',
                'days_left',
                'day_of_month',
                'days_in_month',
            ],
        ]);
});

it('returns 404 when no budget exists', function () {
    $this->getJson('/api/daily-budget', $this->headers)
        ->assertNotFound();
});

it('can update monthly income', function () {
    Budget::factory()->create(['monthly_income' => 1500]);

    $this->putJson('/api/budget', ['monthly_income' => 2500], $this->headers)
        ->assertOk()
        ->assertJsonPath('monthly_income', 2500);

    $this->assertDatabaseHas('budgets', ['monthly_income' => 2500]);
});

it('creates budget if none exists', function () {
    $this->putJson('/api/budget', ['monthly_income' => 3000], $this->headers)
        ->assertOk();

    $this->assertDatabaseHas('budgets', ['monthly_income' => 3000]);
});
