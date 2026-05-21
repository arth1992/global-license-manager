<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\CryptoService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

class InstallLicenseManager extends Command
{
    protected $signature = 'app:install';

    protected $description = 'Interactive server deployment wizard for Global License Manager';

    private string $envPath;

    private string $envExamplePath;

    public function handle(CryptoService $crypto): int
    {
        $this->envPath = base_path('.env');
        $this->envExamplePath = base_path('.env.example');

        $this->printBanner();

        // ── Step 1: System Requirements ──────────────────────────────────────
        if (! $this->checkRequirements()) {
            return self::FAILURE;
        }

        // ── Step 2: Environment File ──────────────────────────────────────────
        $this->setupEnvFile();

        // ── Step 3: Application URL ───────────────────────────────────────────
        $this->configureAppUrl();

        // ── Step 4: Database ──────────────────────────────────────────────────
        if (! $this->configureDatabase()) {
            return self::FAILURE;
        }

        // ── Step 5: Generate App Key ──────────────────────────────────────────
        $this->generateAppKey();

        // ── Step 6: Run Migrations ────────────────────────────────────────────
        if (! $this->runMigrations()) {
            return self::FAILURE;
        }

        // ── Step 7: Generate Ed25519 License Keys ─────────────────────────────
        $this->generateLicenseKeys($crypto);

        // ── Step 8: Create Admin User ─────────────────────────────────────────
        $this->createAdminUser();

        // ── Step 9: Production Optimizations ─────────────────────────────────
        $this->runOptimizations();

        // ── Step 10: Build Assets ─────────────────────────────────────────────
        $this->buildAssets();

        // ── Done ──────────────────────────────────────────────────────────────
        $this->printSuccess();

        return self::SUCCESS;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Banner
    // ──────────────────────────────────────────────────────────────────────────

    private function printBanner(): void
    {
        $this->newLine();
        $this->line('  <fg=cyan;options=bold>╔══════════════════════════════════════════════════╗</>');
        $this->line('  <fg=cyan;options=bold>║    🔐  Global License Manager — Server Setup    ║</>');
        $this->line('  <fg=cyan;options=bold>╚══════════════════════════════════════════════════╝</>');
        $this->newLine();
        $this->line('  This wizard will configure your License Manager instance.');
        $this->line('  It sets up the database, generates Ed25519 cryptographic keys,');
        $this->line('  creates an admin user, and optimizes the application.');
        $this->newLine();
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Step 1 – System Requirements
    // ──────────────────────────────────────────────────────────────────────────

    private function checkRequirements(): bool
    {
        $this->info('⚙  Checking system requirements...');
        $this->newLine();

        $checks = [
            'PHP >= 8.2' => version_compare(PHP_VERSION, '8.2.0', '>='),
            'ext-sodium (Ed25519)' => extension_loaded('sodium'),
            'ext-pdo_mysql' => extension_loaded('pdo_mysql'),
            'ext-mbstring' => extension_loaded('mbstring'),
            'ext-xml' => extension_loaded('xml'),
            'ext-openssl' => extension_loaded('openssl'),
            'storage/ writable' => is_writable(storage_path()),
            'bootstrap/cache/ writable' => is_writable(base_path('bootstrap/cache')),
        ];

        $allPassed = true;

        foreach ($checks as $label => $passed) {
            if ($passed) {
                $this->line("  <fg=green>✓</> $label");
            } else {
                $this->line("  <fg=red>✗</> $label  <fg=red>(REQUIRED)</>");
                $allPassed = false;
            }
        }

        $this->newLine();

        if (! $allPassed) {
            $this->error('One or more system requirements are not met. Please fix them before continuing.');

            return false;
        }

        $this->line('  <fg=green>All requirements satisfied.</>');
        $this->newLine();

        return true;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Step 2 – Environment File
    // ──────────────────────────────────────────────────────────────────────────

    private function setupEnvFile(): void
    {
        $this->info('📄  Setting up environment file...');

        if (File::exists($this->envPath)) {
            $this->line('  <fg=yellow>.env already exists — keeping existing file.</>');
        } else {
            File::copy($this->envExamplePath, $this->envPath);
            $this->line('  <fg=green>.env created from .env.example.</>');
        }

        $this->newLine();
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Step 3 – Application URL
    // ──────────────────────────────────────────────────────────────────────────

    private function configureAppUrl(): void
    {
        $this->info('🌐  Application URL');

        $currentUrl = $this->getEnvValue('APP_URL') ?: 'http://localhost';
        $url = $this->ask("  Enter your application URL (leave blank to keep '{$currentUrl}')", $currentUrl);

        if ($url && $url !== $currentUrl) {
            $this->setEnvValue('APP_URL', $url);
            $this->line("  <fg=green>APP_URL set to: $url</>");
        }

        $this->newLine();
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Step 4 – Database
    // ──────────────────────────────────────────────────────────────────────────

    private function configureDatabase(): bool
    {
        $this->info('🗄  Database Configuration');
        $this->newLine();

        $host = $this->ask('  DB Host', $this->getEnvValue('DB_HOST') ?: '127.0.0.1');
        $port = $this->ask('  DB Port', $this->getEnvValue('DB_PORT') ?: '3306');
        $database = $this->ask('  DB Database', $this->getEnvValue('DB_DATABASE') ?: 'global_license_manager');
        $username = $this->ask('  DB Username', $this->getEnvValue('DB_USERNAME') ?: 'root');
        $password = $this->secret('  DB Password (leave blank if none)') ?: '';

        // Write to .env
        $this->setEnvValue('DB_CONNECTION', 'mysql');
        $this->setEnvValue('DB_HOST', $host);
        $this->setEnvValue('DB_PORT', $port);
        $this->setEnvValue('DB_DATABASE', $database);
        $this->setEnvValue('DB_USERNAME', $username);
        $this->setEnvValue('DB_PASSWORD', $password);

        // Reload config and test connection
        $this->newLine();
        $this->line('  Testing database connection...');

        config([
            'database.connections.mysql.host' => $host,
            'database.connections.mysql.port' => $port,
            'database.connections.mysql.database' => $database,
            'database.connections.mysql.username' => $username,
            'database.connections.mysql.password' => $password,
        ]);

        DB::purge('mysql');

        try {
            DB::connection('mysql')->getPdo();
            $this->line('  <fg=green>✓ Database connection successful!</>');
            $this->newLine();

            return true;
        } catch (\Exception $e) {
            $this->error('  ✗ Database connection failed: '.$e->getMessage());
            $this->newLine();

            return false;
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Step 5 – Application Key
    // ──────────────────────────────────────────────────────────────────────────

    private function generateAppKey(): void
    {
        $this->info('🔑  Application Key');

        $currentKey = $this->getEnvValue('APP_KEY');

        if (! empty($currentKey)) {
            $this->line('  <fg=yellow>APP_KEY already set — skipping.</>');
        } else {
            $this->call('key:generate', ['--ansi' => true]);
            $this->line('  <fg=green>Application key generated.</>');
        }

        $this->newLine();
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Step 6 – Migrations
    // ──────────────────────────────────────────────────────────────────────────

    private function runMigrations(): bool
    {
        $this->info('📦  Running Database Migrations');

        try {
            $this->call('migrate', ['--force' => true, '--ansi' => true]);
            $this->newLine();

            return true;
        } catch (\Exception $e) {
            $this->error('Migration failed: '.$e->getMessage());

            return false;
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Step 7 – Ed25519 License Keys
    // ──────────────────────────────────────────────────────────────────────────

    private function generateLicenseKeys(CryptoService $crypto): void
    {
        $this->info('🔐  Ed25519 License Key Pair');
        $this->newLine();

        $existingPrivate = $this->getEnvValue('LICENSE_PRIVATE_KEY');
        $existingPublic = $this->getEnvValue('LICENSE_PUBLIC_KEY');

        if (! empty($existingPrivate) && ! empty($existingPublic)) {
            $this->line('  <fg=yellow>License keys already set in .env — skipping generation.</>');
            $this->newLine();

            return;
        }

        $this->line('  Generating new Ed25519 key pair...');
        $keys = $crypto->generateKeyPair();

        $this->setEnvValue('LICENSE_PRIVATE_KEY', $keys['private_key']);
        $this->setEnvValue('LICENSE_PUBLIC_KEY', $keys['public_key']);

        $this->newLine();
        $this->line('  <fg=green>✓ Keys generated and saved to .env</>');
        $this->newLine();

        $this->line('  <fg=yellow;options=bold>╔══════════════════════════════════════════════════════════════╗</>');
        $this->line('  <fg=yellow;options=bold>║            IMPORTANT — COPY YOUR PUBLIC KEY                 ║</>');
        $this->line('  <fg=yellow;options=bold>╚══════════════════════════════════════════════════════════════╝</>');
        $this->newLine();
        $this->line('  Embed this PUBLIC KEY in all <fg=cyan>global-admission-manager</> client installs:');
        $this->newLine();
        $this->line("  <fg=green>LICENSE_PUBLIC_KEY={$keys['public_key']}</>");
        $this->newLine();
        $this->warn('  🔐 The PRIVATE KEY stays on THIS server only. Never share it.');
        $this->newLine();

        // Pause so admin can copy the key
        $this->confirm('  Press Enter to continue after noting down the public key', true);
        $this->newLine();
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Step 8 – Admin User
    // ──────────────────────────────────────────────────────────────────────────

    private function createAdminUser(): void
    {
        $this->info('👤  Create Admin User');
        $this->newLine();

        // Check if admin already exists
        if (User::count() > 0) {
            $this->line('  <fg=yellow>Admin users already exist — skipping user creation.</>');
            $this->newLine();

            return;
        }

        $name = $this->ask('  Admin Name', 'Admin');

        $email = $this->askWithValidation(
            '  Admin Email',
            fn ($val) => filter_var($val, FILTER_VALIDATE_EMAIL) ? null : 'Please enter a valid email address.'
        );

        $password = $this->askPasswordWithConfirmation();

        User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        $this->newLine();
        $this->line("  <fg=green>✓ Admin user created: {$name} ({$email})</>");
        $this->newLine();
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Step 9 – Production Optimizations
    // ──────────────────────────────────────────────────────────────────────────

    private function runOptimizations(): void
    {
        $appEnv = $this->getEnvValue('APP_ENV') ?: 'local';

        if ($appEnv === 'production') {
            $this->info('⚡  Optimizing for Production');

            $this->call('config:cache', ['--ansi' => true]);
            $this->call('route:cache', ['--ansi' => true]);
            $this->call('view:cache', ['--ansi' => true]);

            $this->newLine();
            $this->line('  <fg=green>✓ Production caches built.</>');
        } else {
            $this->info('⚡  Optimization');
            $this->call('config:clear', ['--ansi' => true]);
            $this->line('  <fg=yellow>Skipping cache builds — APP_ENV is not production.</>');
        }

        $this->call('storage:link', ['--ansi' => true]);
        $this->newLine();
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Step 10 – Build Assets
    // ──────────────────────────────────────────────────────────────────────────

    private function buildAssets(): void
    {
        $this->info('🎨  Frontend Assets');

        if (! File::exists(base_path('node_modules'))) {
            $this->line('  Installing Node dependencies...');
            exec('npm install 2>&1', $output, $code);

            if ($code !== 0) {
                $this->warn('  npm install failed. Run "npm install && npm run build" manually.');
                $this->newLine();

                return;
            }
        }

        if (! File::exists(public_path('build'))) {
            $this->line('  Building Vite assets...');
            exec('npm run build 2>&1', $output, $code);

            if ($code !== 0) {
                $this->warn('  npm build failed. Run "npm run build" manually.');
            } else {
                $this->line('  <fg=green>✓ Assets built successfully.</>');
            }
        } else {
            $this->line('  <fg=yellow>Build directory already exists — skipping npm build.</>');
        }

        $this->newLine();
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Final Success Banner
    // ──────────────────────────────────────────────────────────────────────────

    private function printSuccess(): void
    {
        $url = $this->getEnvValue('APP_URL') ?: 'http://localhost';

        $this->newLine();
        $this->line('  <fg=green;options=bold>╔══════════════════════════════════════════════════╗</>');
        $this->line('  <fg=green;options=bold>║        ✅  Installation Complete!               ║</>');
        $this->line('  <fg=green;options=bold>╚══════════════════════════════════════════════════╝</>');
        $this->newLine();
        $this->line("  <fg=cyan>Your License Manager is ready at:</> <fg=white;options=bold>{$url}</>");
        $this->newLine();
        $this->line('  <fg=white>Next steps:</>');
        $this->line('  • Start the server:           <fg=green>php artisan serve --port=8001</>');
        $this->line('  • Start the queue worker:     <fg=green>php artisan queue:work</>');
        $this->line('  • Run the scheduler (cron):   <fg=green>* * * * * php artisan schedule:run</>');
        $this->newLine();
        $this->warn('  🔐 Remember: Never expose LICENSE_PRIVATE_KEY from your .env file.');
        $this->newLine();
    }

    // ──────────────────────────────────────────────────────────────────────────
    // .env Helpers
    // ──────────────────────────────────────────────────────────────────────────

    private function getEnvValue(string $key): ?string
    {
        if (! File::exists($this->envPath)) {
            return null;
        }

        $content = File::get($this->envPath);
        preg_match("/^{$key}=(.*)$/m", $content, $matches);

        return isset($matches[1]) ? trim($matches[1], '"\'') : null;
    }

    private function setEnvValue(string $key, string $value): void
    {
        $content = File::exists($this->envPath) ? File::get($this->envPath) : '';

        // Escape special characters for the regex
        $escapedValue = str_contains($value, ' ') ? "\"{$value}\"" : $value;

        if (preg_match("/^{$key}=.*/m", $content)) {
            $content = preg_replace("/^{$key}=.*/m", "{$key}={$escapedValue}", $content);
        } else {
            $content .= "\n{$key}={$escapedValue}";
        }

        File::put($this->envPath, $content);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Input Helpers
    // ──────────────────────────────────────────────────────────────────────────

    private function askWithValidation(string $question, \Closure $validator): string
    {
        while (true) {
            $value = $this->ask($question);
            $error = $validator($value);

            if ($error === null) {
                return $value;
            }

            $this->error("  ✗ {$error}");
        }
    }

    private function askPasswordWithConfirmation(): string
    {
        while (true) {
            $password = $this->secret('  Admin Password (min 8 characters)');

            if (strlen($password) < 8) {
                $this->error('  ✗ Password must be at least 8 characters.');

                continue;
            }

            $confirm = $this->secret('  Confirm Password');

            if ($password !== $confirm) {
                $this->error('  ✗ Passwords do not match. Please try again.');

                continue;
            }

            return $password;
        }
    }
}
