<?php

namespace App\Console\Commands;

use App\Services\CryptoService;
use Illuminate\Console\Command;

class GenerateLicenseKeys extends Command
{
    protected $signature = 'license:generate-keys';

    protected $description = 'Generate a new Ed25519 key pair for license signing. Run once and store keys in .env.';

    public function handle(CryptoService $crypto): int
    {
        $this->warn('⚠️  This will generate a NEW key pair.');
        $this->warn('   Any licenses signed with the OLD private key will no longer be verifiable.');
        $this->newLine();

        if (! $this->confirm('Are you sure you want to generate a new key pair?')) {
            $this->info('Cancelled.');

            return self::SUCCESS;
        }

        $keys = $crypto->generateKeyPair();

        $this->newLine();
        $this->info('✅ Key pair generated successfully!');
        $this->newLine();
        $this->line('Add these to your <comment>.env</comment> file:');
        $this->newLine();
        $this->line('<comment>LICENSE_PRIVATE_KEY=' . $keys['private_key'] . '</comment>');
        $this->newLine();
        $this->line('<comment>LICENSE_PUBLIC_KEY=' . $keys['public_key'] . '</comment>');
        $this->newLine();
        $this->warn('🔐 Keep LICENSE_PRIVATE_KEY secret. Never commit it to version control.');
        $this->info('📋 Copy LICENSE_PUBLIC_KEY and embed it in the global-admission-manager client app.');

        return self::SUCCESS;
    }
}
