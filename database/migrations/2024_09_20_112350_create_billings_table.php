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
        Schema::create('billings', function (Blueprint $table) {
            $table->id();
            $table->enum('payment_method', ['Credit/Debit Card', 'PayPal', 'Account Credit', 'Invoice']);
            $table->string('billing_address');
            $table->string('coupon_code')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('shipment_id')->constrained('shipments')->cascadeOnDelete()->cascadeOnUpdate();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billings');
    }
};
