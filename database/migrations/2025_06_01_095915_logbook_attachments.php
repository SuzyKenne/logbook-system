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
        Schema::create('logbook_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('logbook_entry_id')->constrained('logbook_entries')->onDelete('cascade');
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('restrict');
            $table->string('original_name');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('mime_type');
            $table->unsignedBigInteger('file_size'); // in bytes
            $table->text('description')->nullable();
            $table->enum('attachment_type', ['document', 'image', 'video', 'audio', 'other'])->default('document');
            $table->timestamps();

            $table->index(['logbook_entry_id', 'attachment_type']);
            $table->index(['uploaded_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logbook_attachments');
    }
};
