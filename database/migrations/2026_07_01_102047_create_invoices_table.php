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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('license_id')->constrained()->cascadeOnDelete();
            $table->string('invoice_number')->unique();
            $table->integer('invoice_month');
            $table->integer('invoice_year');
            $table->decimal('base_fee', 10, 2)->default(0);
            $table->integer('applicant_count')->default(0);
            $table->decimal('applicant_fee', 10, 2)->default(0);
            $table->decimal('discount_applied', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->string('status')->default('Unpaid'); // Unpaid, Paid, Waived
            $table->string('pdf_path')->nullable();
            $table->string('receipt_pdf_path')->nullable();
            $table->string('transaction_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
