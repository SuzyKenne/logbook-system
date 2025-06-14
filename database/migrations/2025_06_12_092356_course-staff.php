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
        Schema::create('course_staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->foreignId('staff_id')->constrained('staffs')->onDelete('cascade');

            $table->string('semester')->nullable(); // e.g., '2024-1', '2024-2'
            $table->string('academic_year')->nullable(); // e.g., '2024/2025'
            $table->boolean('is_coordinator')->default(false); // Course coordinator
            $table->enum('status', ['active', 'inactive'])->default('active');

            $table->timestamps();

            // Indexes
            $table->index(['course_id', 'semester']);
            $table->index(['staff_id', 'semester']);
            $table->unique(['course_id', 'staff_id', 'semester'], 'unique_course_staff_semester');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_staff');
    }
};
