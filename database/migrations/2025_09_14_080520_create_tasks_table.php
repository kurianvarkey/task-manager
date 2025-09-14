<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        Schema::create('tasks', function (Blueprint $table) use ($driver) {
            $table->id();
            $table->string('title', 100)->index('tasks_title');
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('status')->index('tasks_status');
            $table->unsignedTinyInteger('priority')->index('tasks_priority');
            $table->date('due_date')->nullable()->index('tasks_due_date');
            $table->unsignedInteger('assigned_to')->nullable()->index('tasks_assigned_to');
            $table->unsignedInteger('version')->index('tasks_version');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('assigned_to')
                ->references('id')->on('users')
                ->nullOnDelete();

            // sqlite doesn't support fulltext. So check if the driver is pgsql or mysql
            match ($driver) {
                'pgsql', 'mysql' => $table->fullText(['title', 'description']),
                default => null,
            };
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
