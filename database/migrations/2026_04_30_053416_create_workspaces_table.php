<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workspaces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('color', 7)->default('#0ea5e9');
            $table->timestamps();

            $table->index('user_id');
        });

        // Add workspace_id to boards (nullable so existing boards aren't broken)
        Schema::table('boards', function (Blueprint $table) {
            $table->foreignId('workspace_id')
                  ->nullable()
                  ->after('user_id')
                  ->constrained('workspaces')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('boards', function (Blueprint $table) {
            $table->dropForeign(['workspace_id']);
            $table->dropColumn('workspace_id');
        });

        Schema::dropIfExists('workspaces');
    }
};
