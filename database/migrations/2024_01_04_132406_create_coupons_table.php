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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->decimal('discount_amount', 20);
            $table->enum('type', ['Percentage', 'Fixed'])->default('percentage');
            $table->enum('target', ['Product', 'Category', 'SubCategory', 'Order'])->default('Order');
            $table->unsignedBigInteger('target_id')->nullable();
            $table->integer('usage_limit');
            $table->dateTime('expires_at');
            $table->boolean('active')->default(true);
            $table->unsignedBigInteger('admin_id');
            $table->foreign('admin_id')->references('id')->on('admins')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
