<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activation_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_id', 20)->unique()->comment('Human-readable ID, e.g. GAM-REQ-9831');
            $table->foreignId('license_id')->nullable()->constrained('licenses')->nullOnDelete();
            $table->string('fingerprint', 512);
            $table->string('domain');
            $table->ipAddress('ip_address');
            $table->enum('status', ['pending', 'used', 'expired'])->default('pending');
            $table->timestamp('expires_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activation_requests');
    }
};
