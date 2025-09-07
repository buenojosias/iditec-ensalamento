<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('current_team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->foreignId('next_team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->string('name');
            $table->string('status', 1)->default('A');
            $table->string('schedule', 3)->nullable();
            $table->timestamps();
        });

        Schema::create('module_student', function (Blueprint $table) {
            $table->foreignId('module_id')->constrained('modules')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->integer('grade')->nullable();
            $table->integer('frequency')->nullable();
            $table->string('situation', 1)->nullable();

            $table->unique(['module_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_student');
        Schema::dropIfExists('students');
    }
};
