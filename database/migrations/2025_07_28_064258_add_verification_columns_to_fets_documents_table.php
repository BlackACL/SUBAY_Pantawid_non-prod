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
        $table->unsignedBigInteger('verified_by')->nullable()->after('status');
        $table->unsignedBigInteger('rejected_by')->nullable()->after('verified_by');
        $table->unsignedBigInteger('approved_by')->nullable()->after('rejected_by');
    });
}

public function down()
{
    Schema::table('fets_documents', function (Blueprint $table) {
        $table->dropColumn(['verified_by', 'rejected_by', 'approved_by']);
    });
}

};
