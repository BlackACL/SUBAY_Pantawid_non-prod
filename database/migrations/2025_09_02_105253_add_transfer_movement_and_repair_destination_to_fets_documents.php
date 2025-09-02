<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */

public function up()
{
    Schema::table('fets_documents', function (Blueprint $table) {
        if (!Schema::hasColumn('fets_documents', 'transfer_movement')) {
            $table->string('transfer_movement')->nullable();
        }
        if (!Schema::hasColumn('fets_documents', 'repair_destination')) {
            $table->string('repair_destination')->nullable();
        }
    });
}


public function down()
{
    Schema::table('fets_documents', function (Blueprint $table) {
        $table->dropColumn(['transfer_movement', 'repair_destination']);
    });
}

};
