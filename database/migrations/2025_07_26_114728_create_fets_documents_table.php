<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('fets_documents', function (Blueprint $table) {
            $table->id();
            $table->string('fets_no')->nullable();
            $table->string('property_no')->nullable(); // single or multiple comma-separated
            $table->string('to_receiver')->nullable();
            $table->string('to_office')->default('Pantawid (RPMO)');
            $table->string('remarks')->nullable();
            $table->string('status')->default('submitted'); // submitted, verified, approved
            $table->string('file_name')->nullable();
            $table->string('file_path')->nullable();
            $table->json('form_data')->nullable();
            $table->unsignedBigInteger('user_id')->nullable(); // employee who filed
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('fets_documents');
    }
};
