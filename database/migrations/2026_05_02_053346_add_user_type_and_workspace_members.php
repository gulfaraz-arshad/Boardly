<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 *
 * Adds:
 *  1. `type` column to `users`          — platform-level role
 *  2. `workspace_members` pivot table    — workspace-level role per user
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Platform-level user type ───────────────────────────
        Schema::table('users', function (Blueprint $table) {
            $table->enum('type', ['super_admin', 'admin', 'member'])
                  ->default('member')
                  ->after('email');
        });

        // ── 2. Workspace-level membership ─────────────────────────
        Schema::create('workspace_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            /**
             * Workspace roles (mirroring Trello):
             *  admin  — can manage boards, lists, invite/remove members, change settings
             *  member — can create/edit cards, comment, upload attachments
             *  viewer — read-only access to all boards inside the workspace
             */
            $table->enum('role', ['admin', 'member', 'viewer'])->default('member');
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();

            $table->unique(['workspace_id', 'user_id']);
            $table->index(['workspace_id', 'role']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workspace_members');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
