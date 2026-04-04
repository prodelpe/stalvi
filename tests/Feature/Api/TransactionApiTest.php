<?php

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['api.token' => 'test-token']);
    $this->headers = ['Authorization' => 'Bearer test-token'];
    $this->account = Account::factory()->create();
    $this->category = Category::factory()->create();
});

it('can list transactions', function () {
    Transaction::factory()->count(3)->create();

    $this->getJson('/api/transactions', $this->headers)
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

it('can create a transaction', function () {
    $this->postJson('/api/transactions', [
        'account_id' => $this->account->id,
        'category_id' => $this->category->id,
        'type' => 'expense',
        'amount' => 25.50,
        'description' => 'Coffee',
        'date' => '2026-04-04',
    ], $this->headers)
        ->assertCreated()
        ->assertJsonPath('data.amount', 25.50)
        ->assertJsonPath('data.description', 'Coffee');

    $this->assertDatabaseHas('transactions', ['description' => 'Coffee']);
});

it('validates required fields on create', function () {
    $this->postJson('/api/transactions', [], $this->headers)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['account_id', 'category_id', 'type', 'amount', 'date']);
});

it('can show a transaction', function () {
    $transaction = Transaction::factory()->create(['description' => 'Test']);

    $this->getJson("/api/transactions/{$transaction->id}", $this->headers)
        ->assertOk()
        ->assertJsonPath('data.description', 'Test');
});

it('can update a transaction', function () {
    $transaction = Transaction::factory()->create(['amount' => 10]);

    $this->putJson("/api/transactions/{$transaction->id}", [
        'amount' => 99.99,
    ], $this->headers)
        ->assertOk()
        ->assertJsonPath('data.amount', 99.99);
});

it('can delete a transaction', function () {
    $transaction = Transaction::factory()->create();

    $this->deleteJson("/api/transactions/{$transaction->id}", [], $this->headers)
        ->assertNoContent();

    $this->assertDatabaseMissing('transactions', ['id' => $transaction->id]);
});

it('can filter by type', function () {
    Transaction::factory()->expense()->count(2)->create();
    Transaction::factory()->income()->create();

    $this->getJson('/api/transactions?type=expense', $this->headers)
        ->assertOk()
        ->assertJsonCount(2, 'data');
});
