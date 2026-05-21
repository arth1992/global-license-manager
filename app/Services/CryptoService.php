<?php

namespace App\Services;

use RuntimeException;

class CryptoService
{
    /**
     * Sign a license payload array using Ed25519 Private Key.
     * Returns a base64url-encoded string: base64(payload_json).base64(signature)
     */
    public function signLicensePayload(array $payload): string
    {
        $privateKeyB64 = config('license.private_key');

        if (empty($privateKeyB64)) {
            throw new RuntimeException('LICENSE_PRIVATE_KEY is not configured in .env');
        }

        $privateKey = base64_decode($privateKeyB64);

        if (strlen($privateKey) !== SODIUM_CRYPTO_SIGN_SECRETKEYBYTES) {
            throw new RuntimeException('LICENSE_PRIVATE_KEY is invalid or malformed.');
        }

        $payloadJson = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $payloadB64  = $this->base64UrlEncode($payloadJson);
        $signature   = sodium_crypto_sign_detached($payloadB64, $privateKey);
        $signatureB64 = $this->base64UrlEncode($signature);

        // Format: <payload_b64>.<signature_b64>
        return $payloadB64.'.'.$signatureB64;
    }

    /**
     * Verify a signed license key and return the decoded payload.
     * Returns null if the signature is invalid or the key is malformed.
     */
    public function verifyAndDecode(string $licenseKey): ?array
    {
        $publicKeyB64 = config('license.public_key');

        if (empty($publicKeyB64)) {
            throw new RuntimeException('LICENSE_PUBLIC_KEY is not configured in .env');
        }

        $publicKey = base64_decode($publicKeyB64);

        if (strlen($publicKey) !== SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES) {
            return null;
        }

        $parts = explode('.', $licenseKey);
        if (count($parts) !== 2) {
            return null;
        }

        [$payloadB64, $signatureB64] = $parts;

        $signature = $this->base64UrlDecode($signatureB64);

        if (! $signature || strlen($signature) !== SODIUM_CRYPTO_SIGN_BYTES) {
            return null;
        }

        $valid = sodium_crypto_sign_verify_detached($signature, $payloadB64, $publicKey);

        if (! $valid) {
            return null;
        }

        $payloadJson = $this->base64UrlDecode($payloadB64);

        if (! $payloadJson) {
            return null;
        }

        return json_decode($payloadJson, true);
    }

    /**
     * Generate a new Ed25519 key pair.
     * Returns ['private_key' => base64string, 'public_key' => base64string]
     */
    public function generateKeyPair(): array
    {
        $keyPair   = sodium_crypto_sign_keypair();
        $secretKey = sodium_crypto_sign_secretkey($keyPair);
        $publicKey = sodium_crypto_sign_publickey($keyPair);

        return [
            'private_key' => base64_encode($secretKey),
            'public_key'  => base64_encode($publicKey),
        ];
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $data): string|false
    {
        $padded = str_pad(strtr($data, '-_', '+/'), strlen($data) + (4 - strlen($data) % 4) % 4, '=');

        return base64_decode($padded, true);
    }
}
