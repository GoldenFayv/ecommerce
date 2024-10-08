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
            $table->enum('mode_of_shipment', ['Air Consolidation', 'Sea Freight (LCL)', 'Inland', 'Door to Airport', 'Door to Seaport', 'Door to Door']);
            $table->enum('priority_level', ['Normal', 'High']);
            $table->enum('status', ['Approved', 'Pending', 'Rejected']);
            $table->enum('cargo_description', ['Box', 'Envelope', 'Pallet', 'Container']);
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->enum('carrier', ['FedEx', 'UPS', 'DHL'])->nullable();
            $table->enum('shipping_method', ['Land', 'Air', 'Ocean', 'Rail']);
            $table->boolean('tracking_service')->default(false);
            $table->boolean('signature_required')->default(false);
            $table->foreignId('courier_id')->constrained('couriers')->OnDelete('set null')->cascadeOnUpdate();
            $table->string('user_name')->nullable();
            $table->string('email')->nullable();
            $table->string('mobile_number')->nullable();
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
