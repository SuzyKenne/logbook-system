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
        Schema::create('logbook_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('logbook_id')->constrained('logbooks')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->date('entry_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('session_title');
            $table->longText('content');
            $table->text('objectives')->nullable();
            $table->text('activities')->nullable();
            $table->text('outcomes')->nullable();
            $table->text('assignments')->nullable();
            $table->text('remarks')->nullable();
            $table->enum('status', ['draft', 'published', 'revised'])->default('draft');
            $table->foreignId('revised_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('revised_at')->nullable();
            $table->timestamps();

            $table->index(['logbook_id', 'entry_date']);
            $table->index(['created_by', 'status']);
            $table->index(['entry_date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logbook_entries');
    }
};
