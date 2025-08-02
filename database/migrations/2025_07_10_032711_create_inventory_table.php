<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('inventory', function (Blueprint $table) {
            $table->id();
            $table->string('FUND_CODE')->nullable();
            $table->string('PROPERTY_STATUS')->nullable();
            $table->string('ARTICLE_DESCRIPTION')->nullable();
            $table->string('GENERAL_DESCRIPTION')->nullable();
            $table->string('SERIAL_NO')->nullable();
            $table->string('PROPERTY_NO')->nullable()->index();
            $table->string('PAR_NO')->nullable();
            $table->string('PAR_DATE')->nullable();
            $table->string('UNIT')->nullable();
            $table->string('QTY')->nullable();
            $table->string('ACQUISITION_COST')->nullable();
            $table->string('ACQUISITION_DATE')->nullable();
            $table->string('RECEIVER')->nullable();
            $table->string('SUBPAR')->nullable();
            $table->string('ACCOUNT_CODE')->nullable();
            $table->string('WARRANTY')->nullable();
            $table->string('OFFICE')->nullable();
            $table->string('FOUND_IN_STATION')->nullable();
            $table->string('LABELLED')->nullable();
            $table->string('DPO_REMARKS')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('inventory');
    }
};
