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
    Schema::table('inventory', function (Blueprint $table) {
        $table->string('source_file')->nullable()->after('RECEIVER');
        $table->timestamp('imported_at')->nullable()->after('source_file');
    });
}

public function down()
{
    Schema::table('inventory', function (Blueprint $table) {
        $table->dropColumn(['source_file', 'imported_at']);
    });
}

};
