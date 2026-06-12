<?php

use App\Domains\Projects\Enums\TaskStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->longText('description')->nullable();
            $table->string('status', 20)->default(TaskStatus::Planned->value);
            $table->date('due_date')->nullable();

            // Polymorphic relation to any model (Project, etc.)
            $table->string('taskable_type')->nullable();
            $table->unsignedBigInteger('taskable_id')->nullable();

            $table->foreignId('assigned_user_id')->nullable()->constrained('users');
            $table->foreignId('creator_id')->nullable()->constrained('users');

            $table->boolean('is_recurring')->default(false);
            $table->string('recurrence_interval', 20)->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['taskable_type', 'taskable_id']);
            $table->index('status');
            $table->index('due_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
