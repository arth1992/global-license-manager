<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('license_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('license_id')->constrained('licenses')->cascadeOnDelete();
            $table->string('event', 100)->comment('created, key_generated, activated, daily_ping, suspended, revoked, reactivated');
            $table->ipAddress('ip_address')->nullable();
            $table->string('fingerprint', 512)->nullable();
            $table->boolean('is_success')->default(true);
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('license_logs');
    }
};
