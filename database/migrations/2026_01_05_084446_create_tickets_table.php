<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUUid('project_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->default('open');
            $table->date('due_date')->nullable();
            $table->double('display_order')->default(0);
            $table->timestamps();
        });

        Schema::create('ticket_user', function (Blueprint $table) {
            $table->id();
            $table->foreignUUid('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignUUid('user_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // 'assignee' or 'reviewer'
            $table->timestamps();

            $table->unique(['ticket_id', 'user_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
