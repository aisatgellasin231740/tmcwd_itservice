<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number', 20)->unique(); // TMCWD-2026-00001
            $table->string('title');
            $table->text('description');

            $table->foreignId('requester_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('department_id')->constrained('departments');
            $table->foreignId('category_id')->constrained('categories');
            $table->foreignId('priority_id')->constrained('priorities');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();

            $table->enum('status', ['open', 'in_progress', 'on_hold', 'resolved', 'closed'])
                  ->default('open');

            $table->dateTime('sla_due_at')->nullable();
            $table->dateTime('sla_paused_at')->nullable();
            $table->unsignedBigInteger('sla_paused_seconds')->default(0); // accumulated pause time

            $table->dateTime('resolved_at')->nullable();
            $table->dateTime('closed_at')->nullable();

            $table->timestamps();

            // Indexes for common filter queries
            $table->index('status');
            $table->index('priority_id');
            $table->index('department_id');
            $table->index('assigned_to');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
