<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('licenses', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('client_name');
            $table->string('client_email');
            $table->integer('max_tenants')->default(1);
            $table->json('features')->nullable();
            $table->enum('status', ['pending', 'active', 'suspended', 'revoked'])->default('pending');
            $table->string('fingerprint', 512)->nullable()->comment('Locked to machine fingerprint on first activation');
            $table->string('domain')->nullable()->comment('Locked to client domain on first activation');
            $table->text('license_key')->nullable()->comment('Signed JWT payload — generated on demand by admin');
            $table->timestamp('expires_at');
            $table->timestamp('activated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('licenses');
    }
};
