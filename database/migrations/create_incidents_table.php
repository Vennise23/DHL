<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->text('description')->nullable();

            // Workflow status
            $table->enum('status', ['draft', 'reviewed', 'published', 'rejected'])
                ->default('draft');

            // Priority (important for DHL scenario)
            $table->enum('priority', ['low', 'medium', 'high'])
                ->default('medium');

            // Source channel (RPA requirement)
            $table->enum('source', ['email', 'telegram', 'teams', 'manual', 'rpa'])
                ->default('manual');


            // Category for better organization by AI
            $table->string('category')->nullable();

            // UiPath gneerate hash for duplicate detection
            $table->string('content_hash')->nullable()->unique();

            // Assigned to
            $table->foreignId('assigned_to')->nullable()->constrained('users');

            // Tracking users
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');

            $table->timestamps();
        });

        Schema::create('incident_attachments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('incident_id')->constrained()->onDelete('cascade');

            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type'); // pdf, docx, png

            $table->timestamps();
        });

        Schema::create('incident_status_histories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('incident_id')->constrained()->onDelete('cascade');

            $table->enum('status', ['draft', 'reviewed', 'published', 'rejected']);

            $table->foreignId('changed_by')->constrained('users');

            $table->text('note')->nullable();

            $table->timestamps();
        });

        Schema::create('rpa_logs', function (Blueprint $table) {

            $table->id();

            $table->foreignId('incident_id')
                ->nullable()
                ->constrained()
                ->onDelete('cascade');

            $table->string('source_type');

            $table->string('action');
            // created
            // duplicate_skipped
            // failed
            // updated

            $table->string('status')->default('success');

            $table->text('message')->nullable();

            $table->string('file_hash')->nullable();

            $table->text('log_file_path')->nullable();

            $table->string('screenshot_path')->nullable();

            $table->string('external_source_id')->nullable();

            $table->timestamps();
        });

        Schema::create('ai_processings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('incident_id')->constrained()->onDelete('cascade');

            $table->text('ai_summary')->nullable();
            $table->text('ai_tags')->nullable();
            $table->text('ai_suggestions')->nullable();

            $table->boolean('conflict_flag')->default(false);
            $table->text('ai_confidence')->nullable();
            $table->text('ai_input_type')->nullable(); // text/image/mixed

            $table->timestamps();
        });
    }
};
