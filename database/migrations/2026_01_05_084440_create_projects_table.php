<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUUid('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('display_name');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'name']);
        });

        Schema::create('project_user', function (Blueprint $table) {
            $table->id();
            $table->foreignUUid('project_id')->constrained()->cascadeOnDelete();
            $table->foreignUUid('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['project_id', 'user_id']);
        });

        Schema::create('project_team', function (Blueprint $table) {
            $table->id();
            $table->foreignUUid('project_id')->constrained()->cascadeOnDelete();
            $table->foreignUUid('team_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['project_id', 'team_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
