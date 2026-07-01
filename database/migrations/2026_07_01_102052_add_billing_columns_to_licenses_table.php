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
            $table->decimal('billing_discount_amount', 10, 2)->nullable()->after('domain');
            $table->string('billing_discount_type')->nullable()->after('billing_discount_amount'); // 'fixed' or 'percentage'
            $table->boolean('is_billing_waived')->default(false)->after('billing_discount_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('licenses', function (Blueprint $table) {
            $table->dropColumn(['billing_discount_amount', 'billing_discount_type', 'is_billing_waived']);
        });
    }
};
