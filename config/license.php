<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Ed25519 License Key Pair
    |--------------------------------------------------------------------------
    | These keys are used to sign and verify cryptographic license payloads.
    | The private key must ONLY exist on this License Server.
    | The public key is embedded in all client Global Admission Manager installs.
    |
    | Generate a fresh key pair with: php artisan license:generate-keys
    */
    'private_key' => env('LICENSE_PRIVATE_KEY', ''),
    'public_key'  => env('LICENSE_PUBLIC_KEY', ''),
];
