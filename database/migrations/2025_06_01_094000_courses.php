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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained('departments')->onDelete('cascade');
            $table->foreignId('level_id')->constrained('levels')->onDelete('cascade');
            $table->string('course_code'); // e.g., "CSC101", "MATH201"
            $table->string('course_name'); // e.g., "Introduction to Programming"
            $table->text('description')->nullable();
            $table->integer('credit_hours')->default(3);
            $table->integer('contact_hours')->default(3); // Hours per week
            $table->enum('course_type', ['core', 'elective', 'general', 'lab'])->default('core');
            $table->enum('semester', ['first', 'second'])->default('first');
            $table->integer('academic_year'); // e.g., 2024
            $table->enum('status', ['active', 'inactive', 'completed', 'cancelled'])->default('active');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Indexes for better performance with custom shorter names
            $table->unique(['department_id', 'level_id', 'course_code', 'academic_year', 'semester'], 'courses_unique_constraint');
            $table->index(['department_id', 'level_id', 'status'], 'courses_dept_level_status_idx');
            $table->index(['academic_year', 'semester'], 'courses_year_semester_idx');
            $table->index(['course_code', 'status'], 'courses_code_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
