<?php

use App\Models\User;
use App\Models\License;
use App\Models\ActivationRequest;
use App\Models\LicenseLog;
use App\Services\CryptoService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create();
    $this->crypto = new CryptoService();
    $this->keys = $this->crypto->generateKeyPair();

    config([
        'license.private_key' => $this->keys['private_key'],
        'license.public_key'  => $this->keys['public_key'],
    ]);
});

it('displays the dashboard overview for authenticated admin', function () {
    $license = License::create([
        'client_name'  => 'Dashboard Client',
        'client_email' => 'dash@client.com',
        'max_tenants'  => 3,
        'status'       => 'active',
        'expires_at'   => now()->addYear(),
    ]);

    $license->log('created', '127.0.0.1', null, true, 'Mock log note');

    $response = $this->actingAs($this->admin)
        ->get('/dashboard');

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->has('stats')
            ->has('recentLogs')
        );
});

it('lists licenses and supports filtering', function () {
    License::create([
        'client_name'  => 'Alpha Academy',
        'client_email' => 'alpha@client.com',
        'max_tenants'  => 2,
        'status'       => 'active',
        'expires_at'   => now()->addYear(),
    ]);

    License::create([
        'client_name'  => 'Beta College',
        'client_email' => 'beta@client.com',
        'max_tenants'  => 4,
        'status'       => 'suspended',
        'expires_at'   => now()->addYear(),
    ]);

    $response = $this->actingAs($this->admin)
        ->get('/licenses?search=Alpha');

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Licenses/Index')
            ->has('licenses.data', 1)
            ->where('licenses.data.0.client_name', 'Alpha Academy')
        );

    $responseFiltered = $this->actingAs($this->admin)
        ->get('/licenses?status=suspended');

    $responseFiltered->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Licenses/Index')
            ->has('licenses.data', 1)
            ->where('licenses.data.0.client_name', 'Beta College')
        );
});

it('shows license detail drilldown', function () {
    $license = License::create([
        'client_name'  => 'Detail Client',
        'client_email' => 'detail@client.com',
        'max_tenants'  => 10,
        'status'       => 'active',
        'expires_at'   => now()->addYear(),
    ]);

    $response = $this->actingAs($this->admin)
        ->get("/licenses/{$license->uuid}");

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Licenses/Show')
            ->has('license')
            ->has('activations')
            ->has('logs')
            ->where('license.client_name', 'Detail Client')
        );
});

it('stores a new license in pending state', function () {
    $response = $this->actingAs($this->admin)
        ->post('/licenses', [
            'client_name'  => 'New Client',
            'client_email' => 'new@client.com',
            'max_tenants'  => 5,
            'expires_at'   => now()->addMonths(6)->format('Y-m-d'),
            'features'     => ['sso', 'custom_branding'],
        ]);

    $license = License::where('client_email', 'new@client.com')->first();
    expect($license)->not->toBeNull()
        ->and($license->status)->toBe('pending')
        ->and($license->features)->toContain('sso');

    $response->assertRedirect("/licenses/{$license->uuid}");
});

it('generates signed license key via generate-key endpoint', function () {
    $license = License::create([
        'client_name'  => 'Signed Client',
        'client_email' => 'signed@client.com',
        'max_tenants'  => 10,
        'status'       => 'pending',
        'expires_at'   => now()->addYear(),
    ]);

    $response = $this->actingAs($this->admin)
        ->post("/licenses/{$license->uuid}/generate-key");

    $license->refresh();
    expect($license->license_key)->not->toBeNull()
        ->and($license->license_key)->toContain('.');

    $payload = $this->crypto->verifyAndDecode($license->license_key);
    expect($payload['uuid'])->toBe($license->uuid)
        ->and($payload['client_name'])->toBe('Signed Client');

    $response->assertRedirect();
});

it('updates license status successfully', function () {
    $license = License::create([
        'client_name'  => 'Status Client',
        'client_email' => 'status@client.com',
        'max_tenants'  => 10,
        'status'       => 'active',
        'expires_at'   => now()->addYear(),
    ]);

    $response = $this->actingAs($this->admin)
        ->patch("/licenses/{$license->uuid}/status", [
            'status' => 'suspended',
        ]);

    $license->refresh();
    expect($license->status)->toBe('suspended');

    $this->assertDatabaseHas('license_logs', [
        'license_id' => $license->id,
        'event'      => 'suspended',
    ]);

    $response->assertRedirect();
});
