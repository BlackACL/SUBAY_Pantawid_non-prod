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
        Schema::table('import_progress', function (Blueprint $table) {
            $table->integer('progress')->nullable()->after('processed');
            $table->text('recent')->nullable()->after('progress');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('import_progress', function (Blueprint $table) {
            $table->dropColumn(['progress', 'recent']);
        });
    }
};
