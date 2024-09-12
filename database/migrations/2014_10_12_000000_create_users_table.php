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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->rememberToken();
            $table->string('chat_id');
            $table->string('password');
            $table->string('last_name');
            $table->string('first_name');
            $table->string('mobile_number');
            $table->string('account_number');
            $table->decimal('balance', 20)->default(0);
            $table->string('profile_picture');
            $table->text('fcm_token');
            $table->text('aws_connection_id');
            $table->string('email')->unique();
            $table->string('username')->unique();
            $table->boolean('is_active')->default(1);
            $table->timestamp("deleted_at")->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
