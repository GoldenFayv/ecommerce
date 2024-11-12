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
        Schema::create('shipment_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_no');
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('origin_address_id')->nullable();
            $table->unsignedBigInteger('destination_address_id')->nullable();
            $table->unsignedBigInteger('drop_off_point_id')->nullable();
            $table->text('cargo_description')->nullable();
            $table->string('mod_of_shipment');
            $table->string('types_of_goods')->nullable();
            $table->string('agent_code', 100)->nullable();
            $table->enum('status', ['Pending','Processing','Dropped Off','In Transit','Cancelled','Ready for Pickup','Picked Up','At Customs','Customs Cleared','Out for Delivery','On Hold','Partial Delivery','Returned to Sender','Failed Delivery Attempt','Delayed','Verified','Rejected','Invoiced','Additional Fee Invoiced','Paid','Payment Await Confirmation','Pending Payment','Payment Confirmed'])->default('Pending');
            $table->boolean('verified')->default(false);
            $table->decimal('total_weight', 10, 2)->nullable();
            $table->string('route_code', 50)->nullable();
            $table->enum('route_type', ['local','international']);
            $table->string('shipping_code', 100);
            $table->decimal('estimated_cost', 10, 2)->nullable();
            $table->decimal('total_declared_value', 10, 2)->nullable();
            $table->string('declaration');
            $table->unsignedBigInteger('origin_zone_id')->nullable();
            $table->unsignedBigInteger('destination_zone_id')->nullable();
            $table->decimal('chargeable_weight', 8, 2)->nullable();
            $table->decimal('volumetric_weight', 8, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipment_orders');
    }
};
