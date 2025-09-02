<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('fets_logs', function (Blueprint $table) {
            if (Schema::hasColumn('fets_logs', 'fets_no')) {
                $table->dropColumn('fets_no');
            }
        });
    }

    public function down(): void {
        Schema::table('fets_logs', function (Blueprint $table) {
            $table->string('fets_no')->nullable();
        });
    }
};
