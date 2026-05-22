<?php

use App\Models\License;
use App\Models\ActivationRequest;
use App\Models\LicenseLog;
use App\Services\CryptoService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->crypto = new CryptoService();
    $this->keys = $this->crypto->generateKeyPair();

    config([
        'license.private_key' => $this->keys['private_key'],
        'license.public_key'  => $this->keys['public_key'],
    ]);
});

it('handles handshake successfully for a valid license', function () {
    $license = License::create([
        'client_name'  => 'Client Academy',
        'client_email' => 'academy@client.com',
        'max_tenants'  => 5,
        'status'       => 'active',
        'expires_at'   => now()->addYear(),
    ]);

    $response = $this->postJson('/api/v1/handshake', [
        'client_email' => 'academy@client.com',
        'fingerprint'  => 'hw-fingerprint-12345',
        'domain'       => 'academy.client.com',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure(['status', 'request_id', 'expires_at']);

    $requestId = $response->json('request_id');
    expect($requestId)->toStartWith('GAM-REQ-');

    $this->assertDatabaseHas('activation_requests', [
        'request_id'  => $requestId,
        'license_id'  => $license->id,
        'fingerprint' => 'hw-fingerprint-12345',
        'domain'      => 'academy.client.com',
        'status'      => 'pending',
    ]);

    $this->assertDatabaseHas('license_logs', [
        'license_id' => $license->id,
        'event'      => 'created',
        'is_success' => true,
    ]);
});

it('returns 404 on handshake if email does not exist', function () {
    $response = $this->postJson('/api/v1/handshake', [
        'client_email' => 'nonexistent@email.com',
        'fingerprint'  => 'hw-fingerprint-12345',
        'domain'       => 'academy.client.com',
    ]);

    $response->assertStatus(404)
        ->assertJson([
            'status'  => 'error',
            'message' => 'No license found associated with this email address.',
        ]);
});

it('returns 403 on handshake if license is suspended or revoked', function () {
    $license = License::create([
        'client_name'  => 'Suspended Client',
        'client_email' => 'suspended@client.com',
        'max_tenants'  => 1,
        'status'       => 'suspended',
        'expires_at'   => now()->addYear(),
    ]);

    $response = $this->postJson('/api/v1/handshake', [
        'client_email' => 'suspended@client.com',
        'fingerprint'  => 'hw-fingerprint-12345',
        'domain'       => 'suspended.client.com',
    ]);

    $response->assertStatus(403)
        ->assertJson([
            'status'  => 'error',
            'message' => 'This license is currently suspended.',
        ]);
});

it('verifies a signed license key successfully and binds fingerprint on first verify', function () {
    $license = License::create([
        'client_name'  => 'Client Academy',
        'client_email' => 'academy@client.com',
        'max_tenants'  => 5,
        'status'       => 'active',
        'expires_at'   => now()->addYear(),
    ]);

    // Create the key
    $keyPayload = [
        'uuid'         => $license->uuid,
        'client_name'  => $license->client_name,
        'client_email' => $license->client_email,
        'max_tenants'  => $license->max_tenants,
        'expires_at'   => $license->expires_at->toISOString(),
    ];
    $licenseKey = $this->crypto->signLicensePayload($keyPayload);

    $response = $this->postJson('/api/v1/verify', [
        'license_key' => $licenseKey,
        'fingerprint' => 'hw-fingerprint-12345',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'active',
            'client' => 'Client Academy',
        ]);

    $license->refresh();
    expect($license->fingerprint)->toBe('hw-fingerprint-12345')
        ->and($license->status)->toBe('active')
        ->and($license->activated_at)->not->toBeNull();

    $this->assertDatabaseHas('license_logs', [
        'license_id' => $license->id,
        'event'      => 'activated',
        'is_success' => true,
    ]);
});

it('rejects verification if fingerprint mismatches the stored fingerprint', function () {
    $license = License::create([
        'client_name'  => 'Client Academy',
        'client_email' => 'academy@client.com',
        'max_tenants'  => 5,
        'status'       => 'active',
        'fingerprint'  => 'hw-fingerprint-12345',
        'expires_at'   => now()->addYear(),
    ]);

    $keyPayload = [
        'uuid'         => $license->uuid,
        'client_name'  => $license->client_name,
        'client_email' => $license->client_email,
        'max_tenants'  => $license->max_tenants,
        'expires_at'   => $license->expires_at->toISOString(),
    ];
    $licenseKey = $this->crypto->signLicensePayload($keyPayload);

    $response = $this->postJson('/api/v1/verify', [
        'license_key' => $licenseKey,
        'fingerprint' => 'different-fingerprint-999',
    ]);

    $response->assertStatus(403)
        ->assertJson([
            'status'  => 'revoked',
            'message' => 'License hardware fingerprint mismatch. Dual-activation is prohibited.',
        ]);

    $this->assertDatabaseHas('license_logs', [
        'license_id' => $license->id,
        'event'      => 'daily_ping',
        'is_success' => false,
    ]);
});

it('rejects verification if the signature is invalid or tampered', function () {
    $response = $this->postJson('/api/v1/verify', [
        'license_key' => 'invalid.signature.here',
        'fingerprint' => 'hw-fingerprint-12345',
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'status'  => 'error',
            'message' => 'Cryptographic signature is invalid or tampered.',
        ]);
});
