<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('licenses', function (Blueprint $table) {
            $table->decimal('base_fee', 10, 2)->nullable()->after('is_billing_waived');
            $table->decimal('per_applicant_fee', 10, 2)->nullable()->after('base_fee');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('licenses', function (Blueprint $table) {
            $table->dropColumn(['base_fee', 'per_applicant_fee']);
        });
    }
};
