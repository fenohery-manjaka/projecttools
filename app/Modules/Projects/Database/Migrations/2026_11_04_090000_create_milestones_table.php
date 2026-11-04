<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->date('due_on');
            $table->timestamp('reached_at')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'due_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('milestones');
    }
};
