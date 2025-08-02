<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('fets_logs', function (Blueprint $table) {
            $table->id();
            $table->string('fets_no');
            $table->string('property_no');
            $table->string('action'); // submitted, verified, approved, rejected
            $table->string('actor'); // fullname of person
            $table->string('actor_role'); // Employee, Provincial DPSC, etc.
            $table->text('remarks')->nullable(); // optional
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void {
        Schema::dropIfExists('fets_logs');
    }
};
