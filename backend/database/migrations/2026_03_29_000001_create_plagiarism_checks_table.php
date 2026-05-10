<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plagiarism_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('thesis_id')->constrained()->onDelete('cascade');
            $table->foreignId('checked_by')->constrained('users')->onDelete('cascade');
            $table->decimal('similarity_score', 5, 2); // Overall similarity percentage
            $table->json('matches')->nullable(); // Array of matching theses with scores
            $table->text('checked_content')->nullable(); // Content that was checked
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['thesis_id', 'created_at']);
            $table->index('similarity_score');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plagiarism_checks');
    }
};
