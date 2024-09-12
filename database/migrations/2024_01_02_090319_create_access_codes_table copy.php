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
        Schema::create('access_codes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('admin_id');
            $table->string('code');

            $table->foreign('permission_id')->references('id')->on('permissions')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('admin_id')->references('id')->on('admins')->restrictOnDelete()->cascadeOnUpdate();
            $table->timestamp("expires_at");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('access_codes');
    }
};
