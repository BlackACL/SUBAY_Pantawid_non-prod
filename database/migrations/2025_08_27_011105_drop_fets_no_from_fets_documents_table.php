<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasColumn('fets_documents', 'fets_no')) {
            Schema::table('fets_documents', function (Blueprint $table) {
                $table->dropColumn('fets_no');
            });
        }
    }

    public function down(): void
    {
        Schema::table('fets_documents', function (Blueprint $table) {
            // restore the column if rollback
            $table->string('fets_no')->nullable();
        });
    }
};
