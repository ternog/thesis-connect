<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('theses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->json('authors'); // Multiple authors support
            $table->string('adviser')->nullable();
            $table->year('year');
            $table->string('department');
            $table->string('program');
            $table->enum('academic_level', ['undergraduate', 'graduate']);
            $table->enum('document_type', ['student_thesis', 'faculty_research']);
            $table->text('abstract');
            $table->json('keywords'); // Multiple keywords support
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['pending', 'approved', 'rejected', 'archived'])->default('pending');
            $table->integer('download_count')->default(0);
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for fast search
            $table->index(['title', 'year', 'department', 'program']);
            $table->index(['academic_level', 'document_type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('theses');
    }
};