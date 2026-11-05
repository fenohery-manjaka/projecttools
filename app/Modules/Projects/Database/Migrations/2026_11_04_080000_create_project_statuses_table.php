<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Project statuses become configurable: the five statuses of the prototype are kept
 * as the starting list, and every existing project keeps its status.
 */
return new class extends Migration
{
    /** @var list<array{string, string, string}> name, tone, category */
    private const array DEFAULTS = [
        ['Envisagé', 'secondary', 'planned'],
        ['En cours', 'info', 'active'],
        ['En pause', 'warn', 'paused'],
        ['Terminé', 'ok', 'done'],
        ['Abandonné', 'bad', 'abandoned'],
    ];

    public function up(): void
    {
        Schema::create('project_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60);
            $table->string('tone', 20);
            $table->string('category', 20)->index();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });

        $now = now();

        foreach (self::DEFAULTS as $position => [$name, $tone, $category]) {
            DB::table('project_statuses')->insert(compact('name', 'tone', 'category', 'position') + ['created_at' => $now, 'updated_at' => $now]);
        }

        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('status_id')->nullable()->after('objectives')->constrained('project_statuses')->restrictOnDelete();
        });

        // Until now the status was the category key itself.
        foreach (DB::table('project_statuses')->get(['id', 'category']) as $status) {
            DB::table('projects')->where('status', $status->category)->update(['status_id' => $status->id]);
        }

        DB::table('projects')->whereNull('status_id')->update([
            'status_id' => DB::table('project_statuses')->where('category', 'planned')->value('id'),
        ]);

        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropColumn('status');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('status')->default('planned')->index();
        });

        foreach (DB::table('project_statuses')->get(['id', 'category']) as $status) {
            DB::table('projects')->where('status_id', $status->id)->update(['status' => $status->category]);
        }

        Schema::table('projects', function (Blueprint $table) {
            $table->dropConstrainedForeignId('status_id');
        });

        Schema::dropIfExists('project_statuses');
    }
};
