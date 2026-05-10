<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add program and interests to users table
        Schema::table('users', function (Blueprint $table) {
            $table->string('program')->nullable()->after('department');
            $table->json('interests')->nullable()->after('program'); // For recommendations
            $table->string('student_id')->nullable()->after('email');
            $table->string('faculty_id')->nullable()->after('student_id');
        });

        // Add view_count and last_viewed_at to theses
        Schema::table('theses', function (Blueprint $table) {
            $table->integer('view_count')->default(0)->after('download_count');
            $table->timestamp('last_viewed_at')->nullable()->after('view_count');
        });

        // Create thesis views tracking table
        Schema::create('thesis_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('thesis_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('ip_address', 45);
            $table->text('user_agent')->nullable();
            $table->timestamps();
            
            $table->index(['thesis_id', 'created_at']);
            $table->index('user_id');
        });

        // Create thesis downloads tracking table
        Schema::create('thesis_downloads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('thesis_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('document_id')->constrained()->onDelete('cascade');
            $table->string('ip_address', 45);
            $table->text('user_agent')->nullable();
            $table->timestamps();
            
            $table->index(['thesis_id', 'created_at']);
            $table->index('user_id');
        });

        // Create user favorites table
        Schema::create('user_favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('thesis_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['user_id', 'thesis_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_favorites');
        Schema::dropIfExists('thesis_downloads');
        Schema::dropIfExists('thesis_views');
        
        Schema::table('theses', function (Blueprint $table) {
            $table->dropColumn(['view_count', 'last_viewed_at']);
        });
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['program', 'interests', 'student_id', 'faculty_id']);
        });
    }
};
