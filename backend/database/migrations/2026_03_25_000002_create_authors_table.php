<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('authors', function (Blueprint $table) {
            $table->id();
            $table->string('last_name');
            $table->string('first_name');
            $table->string('middle_initial')->nullable();
            $table->string('full_name'); // Computed: "Last Name, First Name M."
            $table->string('email')->nullable();
            $table->string('department')->nullable();
            $table->enum('author_type', ['student', 'faculty'])->default('student');
            $table->integer('thesis_count')->default(0);
            $table->timestamps();
            
            $table->index(['last_name', 'first_name']);
            $table->index('full_name');
            $table->unique(['last_name', 'first_name', 'middle_initial']);
        });

        Schema::create('author_thesis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->constrained()->onDelete('cascade');
            $table->foreignId('thesis_id')->constrained()->onDelete('cascade');
            $table->integer('order')->default(0); // Author order in thesis
            $table->timestamps();
            
            $table->unique(['author_id', 'thesis_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('author_thesis');
        Schema::dropIfExists('authors');
    }
};
