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
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string("first_name");
            $table->string("last_name");
            $table->string("password");
            $table->string("email");
            $table->string("profile_picture");
            $table->timestamp("last_login");
            $table->boolean("is_active")->default(1);

            $table->unsignedBigInteger("admin_role_id");
            $table->string("connection_id");
            $table->string("chat_id");
            $table->timestamp("deleted_at");

            $table->foreign('admin_role_id')->references('id')->on('admin_roles')->restrictOnDelete()->cascadeOnUpdate();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin');
    }
};
