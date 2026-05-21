<?php

use App\Services\CryptoService;

it('can generate a valid Ed25519 key pair', function () {
    $crypto = new CryptoService;
    $keys = $crypto->generateKeyPair();

    expect($keys)->toHaveKeys(['private_key', 'public_key']);

    $privateBytes = base64_decode($keys['private_key']);
    $publicBytes  = base64_decode($keys['public_key']);

    expect(strlen($privateBytes))->toBe(SODIUM_CRYPTO_SIGN_SECRETKEYBYTES)
        ->and(strlen($publicBytes))->toBe(SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES);
});

it('signs and verifies a license payload correctly', function () {
    $crypto = new CryptoService;
    $keys = $crypto->generateKeyPair();

    // Configure the keys for this test
    config([
        'license.private_key' => $keys['private_key'],
        'license.public_key'  => $keys['public_key'],
    ]);

    $payload = [
        'license_id'    => 'LIC-TEST-001',
        'client_name'   => 'Test University',
        'max_tenants'   => 3,
        'features'      => ['multi-tenant'],
        'expires_at'    => now()->addYear()->toISOString(),
        'issued_at'     => now()->toISOString(),
    ];

    $signed = $crypto->signLicensePayload($payload);

    expect($signed)->toBeString()->toContain('.');

    $decoded = $crypto->verifyAndDecode($signed);

    expect($decoded)->not->toBeNull()
        ->and($decoded['license_id'])->toBe('LIC-TEST-001')
        ->and($decoded['client_name'])->toBe('Test University')
        ->and($decoded['max_tenants'])->toBe(3);
});

it('returns null when a license key is tampered with', function () {
    $crypto = new CryptoService;
    $keys = $crypto->generateKeyPair();

    config([
        'license.private_key' => $keys['private_key'],
        'license.public_key'  => $keys['public_key'],
    ]);

    $payload = ['license_id' => 'LIC-001', 'expires_at' => now()->addYear()->toISOString()];
    $signed  = $crypto->signLicensePayload($payload);

    // Tamper with the payload portion
    [$payloadPart, $sigPart] = explode('.', $signed);
    $tampered = base64_encode('{"license_id":"LIC-FAKE","expires_at":"2099-01-01"}').'.'. $sigPart;

    $result = $crypto->verifyAndDecode($tampered);

    expect($result)->toBeNull();
});

it('returns null for a malformed license key', function () {
    $crypto = new CryptoService;
    $keys   = $crypto->generateKeyPair();
    config(['license.private_key' => $keys['private_key'], 'license.public_key' => $keys['public_key']]);

    expect($crypto->verifyAndDecode('not-a-valid-license-key'))->toBeNull();
    expect($crypto->verifyAndDecode(''))->toBeNull();
    expect($crypto->verifyAndDecode('onlyonepart'))->toBeNull();
});
