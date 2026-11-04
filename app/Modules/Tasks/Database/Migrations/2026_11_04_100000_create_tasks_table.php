<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** @var list<array{string, string, string}> name, tone, category: the statuses of the prototype */
    private const array STATUSES = [
        ['À faire', 'secondary', 'todo'],
        ['En cours', 'info', 'doing'],
        ['Bloqué', 'bad', 'blocked'],
        ['Terminé', 'ok', 'done'],
    ];

    public function up(): void
    {
        Schema::create('task_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60);
            $table->string('tone', 20);
            $table->string('category', 20)->index();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });

        $now = now();

        foreach (self::STATUSES as $position => [$name, $tone, $category]) {
            DB::table('task_statuses')->insert(compact('name', 'tone', 'category', 'position') + ['created_at' => $now, 'updated_at' => $now]);
        }

        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('milestone_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('status_id')->constrained('task_statuses')->restrictOnDelete();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->string('priority', 20)->default('normal');
            $table->date('due_on')->nullable()->index();
            $table->decimal('estimate_hours', 5, 1)->nullable();
            $table->text('blocked_reason')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status_id', 'position']);
        });

        Schema::create('task_user', function (Blueprint $table) {
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['task_id', 'user_id']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_user');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('task_statuses');
    }
};
