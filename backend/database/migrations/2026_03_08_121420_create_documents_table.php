<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('thesis_id')->constrained()->onDelete('cascade');
            $table->string('original_name');
            $table->string('file_path');
            $table->string('file_hash')->unique(); // Prevent duplicate uploads
            $table->bigInteger('file_size');
            $table->string('mime_type');
            $table->integer('version')->default(1);
            $table->boolean('is_active')->default(true);
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            $table->index(['thesis_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};