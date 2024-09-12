<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->foreign('admin_id')->references('id')->on('admins')->cascadeOnUpdate()->cascadeOnDelete();
            $table->decimal('total', 20, 2);
            $table->enum("payment_method", [
                "Card",
                "Bank / Transfer",
                "Cash",
            ]);
            $table->enum("delivery_method", [
                "Pick-Up",
                "Dispatch"
            ]);
            $table->decimal('sub_total', 20, 2);
            $table->decimal('discount', 20, 2);
            $table->enum('status', ['New', 'Processing', 'Completed', 'Returned', 'Cancelled', 'Failed']);
            $table->decimal('delivery_cost', 10, 2);
            $table->enum('payment_status', ['Paid', 'UnPaid']);
            $table->string('reference');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
