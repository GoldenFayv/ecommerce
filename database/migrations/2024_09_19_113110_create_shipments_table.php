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
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->string('shipment_reference')->unique();
            $table->datetime('shipment_date');
            $table->enum('mode_of_shipment', ['Air Consolidation', 'Sea Freight (LCL)', 'Inland', 'Door to Airport', 'Door to Seaport', 'Door to Door']);
            $table->enum('priority_level', ['Normal', 'High']);
            $table->enum('cargo_description', ['Box', 'Envelope', 'Pallet', 'Container']);
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->enum('carrier', ['FedEx', 'UPS', 'DHL']);
            $table->enum('shipping_method', ['Land', 'Air', 'Ocean', 'Rail']);
            $table->boolean('tracking_service')->default(false);
            $table->boolean('signature_required')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
