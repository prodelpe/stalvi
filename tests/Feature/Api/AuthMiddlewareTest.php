<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('rejects requests without token', function () {
    $this->getJson('/api/transactions')
        ->assertUnauthorized();
});

it('rejects requests with wrong token', function () {
    $this->getJson('/api/transactions', ['Authorization' => 'Bearer wrong-token'])
        ->assertUnauthorized();
});

it('accepts requests with valid token', function () {
    config(['api.token' => 'test-token']);

    $this->getJson('/api/transactions', ['Authorization' => 'Bearer test-token'])
        ->assertOk();
});
