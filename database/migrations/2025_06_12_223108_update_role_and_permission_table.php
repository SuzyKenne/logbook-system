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
        Schema::table('permissions', function (Blueprint $table) {
            $table->string('identifier')->nullable()->after('name');
            $table->foreignId('parent_id')->nullable()->constrained('permissions')->onDelete('cascade');
            $table->boolean('status')->default(true)->after('guard_name');
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->string('identifier')->nullable()->after('name');
            $table->boolean('all_permissions')->default(false)->after('identifier');
            $table->boolean('is_active')->default(true)->after('all_permissions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropColumn(['identifier', 'parent_id', 'status']);
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn(['identifier', 'all_permissions', 'is_active']);
        });
    }
};
