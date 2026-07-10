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
        Schema::table('system_settings', function (Blueprint $table) {
            $table->text('company_address')->nullable();
            $table->string('gstin')->nullable();
            $table->string('state_code')->nullable();
            $table->string('state_name')->nullable();
        });

        Schema::table('licenses', function (Blueprint $table) {
            $table->text('billing_address')->nullable();
            $table->string('gstin')->nullable();
            $table->string('state_code')->nullable();
            $table->string('state_name')->nullable();
            $table->decimal('gst_rate', 5, 2)->default(18.00);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('taxable_amount', 10, 2)->default(0.00)->after('discount_applied');
            $table->decimal('cgst_amount', 10, 2)->default(0.00)->after('taxable_amount');
            $table->decimal('sgst_amount', 10, 2)->default(0.00)->after('cgst_amount');
            $table->decimal('igst_amount', 10, 2)->default(0.00)->after('sgst_amount');
            $table->decimal('gst_rate', 5, 2)->default(18.00)->after('igst_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $table->dropColumn(['company_address', 'gstin', 'state_code', 'state_name']);
        });

        Schema::table('licenses', function (Blueprint $table) {
            $table->dropColumn(['billing_address', 'gstin', 'state_code', 'state_name', 'gst_rate']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['taxable_amount', 'cgst_amount', 'sgst_amount', 'igst_amount', 'gst_rate']);
        });
    }
};
