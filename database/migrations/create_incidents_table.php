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
            $table->enum('status', ['draft', 'reviewed', 'published'])
                ->default('draft');

            // Priority (important for DHL scenario)
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])
                ->default('medium');

            // Source channel (RPA requirement)
            $table->enum('source', ['email', 'telegram', 'teams', 'manual', 'rpa'])
                ->default('manual');

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

            $table->enum('status', ['draft', 'reviewed', 'published']);

            $table->foreignId('changed_by')->constrained('users');

            $table->text('note')->nullable();

            $table->timestamps();
        });

        Schema::create('rpa_logs', function (Blueprint $table) {
            $table->id();

            $table->string('source_type'); // email, drive, telegram
            $table->integer('created_count')->default(0);
            $table->integer('duplicate_count')->default(0);
            $table->integer('failed_count')->default(0);

            $table->text('log_file_path')->nullable();

            $table->timestamps();
        });

        Schema::create('ai_processings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('incident_id')->constrained()->onDelete('cascade');

            $table->text('ai_summary')->nullable();
            $table->text('ai_tags')->nullable();
            $table->text('ai_suggestions')->nullable();

            $table->boolean('conflict_flag')->default(false);

            $table->timestamps();
        });
    }
};
