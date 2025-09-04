<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('officials', function (Blueprint $table) {
            $table->id();
            $table->string('province')->nullable();   // null = not tied to province (e.g., Regional, Head of Property)
            $table->string('role');                   // Provincial DPSC, Regional DPSC, Head of Property
            $table->string('fullname');               // official’s full name
            $table->boolean('active')->default(true); // allow history (mark inactive when replaced)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('officials');
    }
};
