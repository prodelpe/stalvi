<?php

use App\Models\Account;
use App\Models\Allocation;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['api.token' => 'test-token']);
    $this->headers = ['Authorization' => 'Bearer test-token'];
    $this->source = Account::factory()->create(['name' => 'Current']);
    $this->destination = Account::factory()->create(['name' => 'Savings']);
});

it('can list allocations', function () {
    Allocation::factory()->count(2)->create();

    $this->getJson('/api/allocations', $this->headers)
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

it('can create an allocation', function () {
    $this->postJson('/api/allocations', [
        'source_account_id' => $this->source->id,
        'destination_account_id' => $this->destination->id,
        'amount' => 500,
    ], $this->headers)
        ->assertCreated()
        ->assertJsonPath('data.amount', 500);

    $this->assertDatabaseHas('allocations', ['amount' => 500]);
});

it('rejects same source and destination', function () {
    $this->postJson('/api/allocations', [
        'source_account_id' => $this->source->id,
        'destination_account_id' => $this->source->id,
        'amount' => 100,
    ], $this->headers)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['destination_account_id']);
});

it('can update an allocation', function () {
    $allocation = Allocation::factory()->create(['amount' => 100]);

    $this->putJson("/api/allocations/{$allocation->id}", [
        'amount' => 750,
    ], $this->headers)
        ->assertOk()
        ->assertJsonPath('data.amount', 750);
});

it('can delete an allocation', function () {
    $allocation = Allocation::factory()->create();

    $this->deleteJson("/api/allocations/{$allocation->id}", [], $this->headers)
        ->assertNoContent();

    $this->assertDatabaseMissing('allocations', ['id' => $allocation->id]);
});
