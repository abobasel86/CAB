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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            
            // Imported fields
            $table->date('post_date')->nullable();
            $table->date('value_date')->nullable();
            $table->text('description')->nullable();
            $table->string('doctor_name')->nullable();
            $table->string('reference')->nullable();
            $table->decimal('amount', 15, 2)->nullable();
            $table->decimal('balance', 15, 2)->nullable();
            $table->decimal('specialist', 15, 2)->nullable()->default(0);
            
            // Manual fields
            $table->decimal('registration', 15, 2)->nullable()->default(0);
            $table->decimal('yearly', 15, 2)->nullable()->default(0);
            $table->decimal('exam', 15, 2)->nullable()->default(0);
            $table->decimal('certificate', 15, 2)->nullable()->default(0);
            $table->decimal('newsletters', 15, 2)->nullable()->default(0);
            $table->decimal('other', 15, 2)->nullable()->default(0);
            $table->decimal('visa', 15, 2)->nullable()->default(0);
            
            // Additional fields
            $table->string('inward_number')->nullable();
            $table->date('inward_date')->nullable();
            $table->text('notes')->nullable();
            
            // Status fields
            $table->boolean('is_locked')->default(false);
            $table->foreignId('completed_by_user_id')->nullable()->constrained('users');
            $table->timestamp('completed_at')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
