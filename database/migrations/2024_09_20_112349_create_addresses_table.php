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
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            // $table->string('type');
            $table->string('mobile_number');
            $table->string('email');
            $table->string('street_address');
            $table->string('lga');
            $table->string('city');
            $table->string('state');
            $table->string('postal_code');
            $table->string('country');
            $table->datetime('pickup_dropoff_address')->nullable();
            $table->datetime('preferred_datetime')->nullable();
            $table->string('special_instructions')->nullable();
            $table->string('type');
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('shipment_id')->constrained('shipments')->cascadeOnDelete()->cascadeOnUpdate();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
