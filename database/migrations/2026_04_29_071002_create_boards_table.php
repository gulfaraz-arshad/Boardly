<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ============================================================
// 2024_01_01_000001_create_boards_table.php
// ============================================================
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('color', 7)->default('#0ea5e9'); // hex color
            $table->string('cover_image')->nullable();
            $table->boolean('is_public')->default(false);
            $table->timestamps();

            $table->index(['user_id']);
        });

        // --------------------------------------------------------
        // 2024_01_01_000002_create_board_members_table.php
        // --------------------------------------------------------
        Schema::create('board_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('board_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('role', ['owner', 'admin', 'member', 'viewer'])->default('member');
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();

            $table->unique(['board_id', 'user_id']);
            $table->index('board_id');
        });

        // --------------------------------------------------------
        // 2024_01_01_000003_create_board_invitations_table.php
        // --------------------------------------------------------
        Schema::create('board_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('board_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invited_by')->constrained('users')->cascadeOnDelete();
            $table->string('email');
            $table->string('token', 64)->unique();
            $table->enum('role', ['admin', 'member', 'viewer'])->default('member');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->index(['email', 'token']);
        });

        // --------------------------------------------------------
        // 2024_01_01_000004_create_board_lists_table.php
        // --------------------------------------------------------
        Schema::create('board_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('board_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('position')->default(0);
            $table->string('color', 7)->nullable();
            $table->boolean('is_archived')->default(false);
            $table->timestamps();

            $table->index(['board_id', 'position']);
        });

        // --------------------------------------------------------
        // 2024_01_01_000005_create_labels_table.php
        // --------------------------------------------------------
        Schema::create('labels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('board_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('color', 7); // hex color
            $table->timestamps();

            $table->index('board_id');
        });

        // --------------------------------------------------------
        // 2024_01_01_000006_create_cards_table.php
        // --------------------------------------------------------
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('board_list_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users');
            $table->string('title');
            $table->longText('description')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamp('due_date')->nullable();
            $table->boolean('is_completed')->default(false);
            $table->boolean('is_archived')->default(false);
            $table->string('cover_color', 7)->nullable();
            $table->timestamps();

            $table->index(['board_list_id', 'position', 'is_archived']);
            $table->fullText(['title', 'description']);
        });

        // --------------------------------------------------------
        // 2024_01_01_000007_create_card_label_table.php
        // --------------------------------------------------------
        Schema::create('card_label', function (Blueprint $table) {
            $table->foreignId('card_id')->constrained()->cascadeOnDelete();
            $table->foreignId('label_id')->constrained()->cascadeOnDelete();
            $table->primary(['card_id', 'label_id']);
        });

        // --------------------------------------------------------
        // 2024_01_01_000008_create_card_members_table.php
        // --------------------------------------------------------
        Schema::create('card_members', function (Blueprint $table) {
            $table->foreignId('card_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->primary(['card_id', 'user_id']);
        });

        // --------------------------------------------------------
        // 2024_01_01_000009_create_card_attachments_table.php
        // --------------------------------------------------------
        Schema::create('card_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->constrained('users');
            $table->string('filename');
            $table->string('original_name');
            $table->string('mime_type');
            $table->unsignedBigInteger('size'); // bytes
            $table->string('disk')->default('public');
            $table->timestamps();

            $table->index('card_id');
        });

        // --------------------------------------------------------
        // 2024_01_01_000010_create_card_checklists_table.php
        // --------------------------------------------------------
        Schema::create('card_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_id')->constrained()->cascadeOnDelete();
            $table->string('title')->default('Checklist');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::create('checklist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_checklist_id')->constrained()->cascadeOnDelete();
            $table->string('content');
            $table->boolean('is_checked')->default(false);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });

        // --------------------------------------------------------
        // 2024_01_01_000011_create_card_activities_table.php
        // --------------------------------------------------------
        Schema::create('card_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // moved, created, updated, commented, attached, etc.
            $table->text('content'); // human-readable description
            $table->json('metadata')->nullable(); // extra data
            $table->timestamps();

            $table->index(['card_id', 'created_at']);
        });

        // --------------------------------------------------------
        // 2024_01_01_000012_create_card_comments_table.php
        // --------------------------------------------------------
        Schema::create('card_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();

            $table->index(['card_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('card_comments');
        Schema::dropIfExists('card_activities');
        Schema::dropIfExists('checklist_items');
        Schema::dropIfExists('card_checklists');
        Schema::dropIfExists('card_attachments');
        Schema::dropIfExists('card_members');
        Schema::dropIfExists('card_label');
        Schema::dropIfExists('cards');
        Schema::dropIfExists('labels');
        Schema::dropIfExists('board_lists');
        Schema::dropIfExists('board_invitations');
        Schema::dropIfExists('board_members');
        Schema::dropIfExists('boards');
    }
};
