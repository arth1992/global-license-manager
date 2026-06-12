<?php

use App\Models\License;
use App\Models\Release;
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

    $this->license = License::create([
        'client_name'  => 'Client Academy',
        'client_email' => 'academy@client.com',
        'max_tenants'  => 5,
        'status'       => 'active',
        'fingerprint'  => 'hw-fingerprint-12345',
        'expires_at'   => now()->addYear(),
    ]);

    $keyPayload = [
        'uuid'         => $this->license->uuid,
        'client_name'  => $this->license->client_name,
        'client_email' => $this->license->client_email,
        'max_tenants'  => $this->license->max_tenants,
        'expires_at'   => $this->license->expires_at->toISOString(),
    ];
    $this->licenseKey = $this->crypto->signLicensePayload($keyPayload);
});

it('rejects update check for tampered/invalid signature', function () {
    $response = $this->postJson('/api/v1/update/check', [
        'license_key'     => 'invalid.signature.key',
        'fingerprint'     => 'hw-fingerprint-12345',
        'current_version' => '1.0.0',
    ]);

    $response->assertStatus(422)
        ->assertJsonPath('status', 'error');
});

it('rejects update check if fingerprint mismatches', function () {
    $response = $this->postJson('/api/v1/update/check', [
        'license_key'     => $this->licenseKey,
        'fingerprint'     => 'wrong-fingerprint',
        'current_version' => '1.0.0',
    ]);

    $response->assertStatus(403)
        ->assertJsonPath('status', 'revoked');
});

it('returns update_available false if no newer versions exist', function () {
    Release::create([
        'version'   => '1.0.0',
        'changelog' => 'Initial release',
        'zip_path'  => 'updates/release-1.0.0.zip',
        'signature' => 'mock-signature',
        'size'      => 1024,
    ]);

    $response = $this->postJson('/api/v1/update/check', [
        'license_key'     => $this->licenseKey,
        'fingerprint'     => 'hw-fingerprint-12345',
        'current_version' => '1.0.0',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'status'           => 'success',
            'update_available' => false,
        ]);
});

it('returns update_available true and release details if a newer version exists', function () {
    Release::create([
        'version'   => '1.1.0',
        'changelog' => 'New feature added',
        'zip_path'  => 'updates/release-1.1.0.zip',
        'signature' => 'mock-sig-1.1.0',
        'size'      => 2048,
    ]);

    $response = $this->postJson('/api/v1/update/check', [
        'license_key'     => $this->licenseKey,
        'fingerprint'     => 'hw-fingerprint-12345',
        'current_version' => '1.0.0',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'status'           => 'success',
            'update_available' => true,
            'latest_version'   => '1.1.0',
            'changelog'        => 'New feature added',
            'signature'        => 'mock-sig-1.1.0',
            'size'             => 2048,
        ]);

    expect($response->json('zip_url'))->toEndWith('storage/updates/release-1.1.0.zip');
});
