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
        Schema::create('field_settings', function (Blueprint $table) {
            $table->id();
            $table->string('field_name')->unique();
            $table->enum('field_type', ['imported', 'manual', 'calculated'])->default('manual');
            $table->boolean('is_editable')->default(true);
            $table->integer('display_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('field_settings');
    }
};
