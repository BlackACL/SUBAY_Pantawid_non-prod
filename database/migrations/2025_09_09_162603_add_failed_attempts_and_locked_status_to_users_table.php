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
        Schema::table('users', function (Blueprint $table) {
            // ✅ Add failed_attempts if it doesn't exist
            if (!Schema::hasColumn('users', 'failed_attempts')) {
                $table->unsignedInteger('failed_attempts')->default(0)->after('password');
            }

            // ✅ Ensure locked_status column exists and has default "No"
            if (Schema::hasColumn('users', 'locked_status')) {
                $table->string('locked_status', 10)->default('No')->change();
            } else {
                $table->string('locked_status', 10)->default('No')->after('failed_attempts');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'failed_attempts')) {
                $table->dropColumn('failed_attempts');
            }
            // ⚠️ don’t drop locked_status since it already exists in your DB
        });
    }
};
