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
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->integer('number_of_packages');
            $table->string('package_description');
            $table->float('weight');
            $table->float('length');
            $table->float('width');
            $table->float('height');
            $table->float('shipment_value');
            $table->boolean('insurance')->default(false);
            $table->string('shipment_contents');
            $table->boolean('fragile')->default(false);
            $table->boolean('hazardous_materials')->default(false);
            $table->foreignId('shipment_id')->constrained('shipments')->cascadeOnDelete()->cascadeOnUpdate();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
