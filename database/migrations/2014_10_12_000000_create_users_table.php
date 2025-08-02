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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('fullname');
            $table->string('username');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('employee_status', ['Contractual', 'Regular', 'MOA']);
            $table->string('office');
            $table->enum('region', ['Region XI']);
            $table->string('province');
            $table->string('municipality');
            $table->enum('access_level', ['Superadmin', 'Regional DPSC', 'Provincial DPSC', 'Employee']);
            $table->enum('activated', ['Yes', 'No']);
            $table->enum('locked_status', ['Yes', 'No']);
            $table->enum('deleted_status', ['Yes', 'No']);
            $table->rememberToken();
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
