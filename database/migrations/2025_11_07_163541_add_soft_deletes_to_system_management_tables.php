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
        // Add soft deletes to officials table
        Schema::table('officials', function (Blueprint $table) {
            $table->softDeletes();
        });

        // Add soft deletes to places table
        Schema::table('places', function (Blueprint $table) {
            $table->softDeletes();
        });

        // Add soft deletes to repair_destinations table
        Schema::table('repair_destinations', function (Blueprint $table) {
            $table->softDeletes();
        });

        // Add soft deletes to manuals table
        Schema::table('manuals', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove soft deletes from officials table
        Schema::table('officials', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        // Remove soft deletes from places table
        Schema::table('places', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        // Remove soft deletes from repair_destinations table
        Schema::table('repair_destinations', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        // Remove soft deletes from manuals table
        Schema::table('manuals', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
