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
        Schema::create('temporary_users', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('number')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('verifiction_code');
            $table->timestamp('expires_at')->nullable(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temporary_users');
    }
};
