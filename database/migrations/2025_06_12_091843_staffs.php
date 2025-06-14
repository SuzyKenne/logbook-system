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
        Schema::create('staffs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('faculty_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('department_id')->nullable()->constrained()->onDelete('set null');

            $table->enum('staff_type', ['dean', 'hod', 'lecturer'])->default('lecturer');
            $table->string('staff_id')->unique()->nullable();
            $table->string('designation')->nullable();
            $table->string('qualification')->nullable();
            $table->string('specialization')->nullable();
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->string('office_location')->nullable();
            $table->string('office_phone')->nullable();
            $table->text('bio')->nullable();

            $table->boolean('is_head_of_department')->default(false);
            $table->boolean('is_dean')->default(false);

            $table->timestamps();

            // Indexes
            $table->index(['faculty_id', 'staff_type']);
            $table->index(['department_id', 'staff_type']);
            $table->index(['status', 'staff_type']);

            // Constraints
            $table->unique(['department_id', 'is_head_of_department'], 'unique_hod_per_department')
                ->where('is_head_of_department', true);
            $table->unique(['faculty_id', 'is_dean'], 'unique_dean_per_faculty')
                ->where('is_dean', true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staffs');
    }
};
