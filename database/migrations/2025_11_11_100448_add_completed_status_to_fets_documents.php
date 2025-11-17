<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add 'completed' to the status enum
        DB::statement("ALTER TABLE fets_documents MODIFY COLUMN status ENUM('submitted', 'verified', 'approved', 'rejected', 'completed') DEFAULT 'submitted'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'completed' from the status enum (revert to original)
        DB::statement("ALTER TABLE fets_documents MODIFY COLUMN status ENUM('submitted', 'verified', 'approved', 'rejected') DEFAULT 'submitted'");
    }
};
