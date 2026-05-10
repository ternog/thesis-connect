<?php

use App\Http\Controllers\Api\ActivityLogController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AuthorController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\PlagiarismController;
use App\Http\Controllers\Api\RecommendationController;
use App\Http\Controllers\Api\SmartSearchController;
use App\Http\Controllers\Api\ThesisController;
use App\Http\Controllers\Api\UserController;
use App\Models\Thesis;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Public thesis search (approved only)
Route::get('/theses', [ThesisController::class, 'index']);
Route::get('/theses/{thesis}', [ThesisController::class, 'show']);
Route::get('/theses/filters/options', [ThesisController::class, 'getFilters']);

// Public categories
Route::get('/categories', [CategoryController::class, 'index']);

// Public authors
Route::get('/authors', [AuthorController::class, 'index']);
Route::get('/authors/{author}', [AuthorController::class, 'show']);

// Public recommendations
Route::get('/recommendations/trending', [RecommendationController::class, 'trending']);
Route::get('/recommendations/popular', [RecommendationController::class, 'popular']);
Route::get('/theses/{thesis}/related', [RecommendationController::class, 'related']);

// Smart search (public)
Route::get('/search', [SmartSearchController::class, 'search']);
Route::get('/search/autocomplete', [SmartSearchController::class, 'autocomplete']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Thesis management
    Route::apiResource('theses', ThesisController::class)->except(['index', 'show']);
    Route::post('/theses/{thesis}/approve', [ThesisController::class, 'approve']);
    Route::post('/theses/{thesis}/reject', [ThesisController::class, 'reject']);
    Route::post('/theses/check-plagiarism', [ThesisController::class, 'checkPlagiarism']);

    // Document management
    Route::post('/theses/{thesis}/documents', [DocumentController::class, 'store']);
    Route::get('/theses/{thesis}/documents/versions', [DocumentController::class, 'versions']);
    Route::delete('/documents/{document}', [DocumentController::class, 'destroy']);

    // Category management (admin only)
    Route::apiResource('categories', CategoryController::class)->except(['index']);

    // User management (admin only)
    Route::apiResource('users', UserController::class);
    Route::get('/roles', [UserController::class, 'getRoles']);
    Route::get('/users/pending/approvals', [UserController::class, 'pendingApprovals']);
    Route::post('/users/{user}/approve', [UserController::class, 'approve']);

    // Dashboard and statistics
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
    Route::get('/dashboard/activity', [DashboardController::class, 'recentActivity']);
    Route::get('/dashboard/charts', [DashboardController::class, 'chartData']);
    Route::get('/dashboard/user-stats', [DashboardController::class, 'userStats']);

    // Author management
    Route::post('/authors', [AuthorController::class, 'store']);
    Route::put('/authors/{author}', [AuthorController::class, 'update']);
    Route::delete('/authors/{author}', [AuthorController::class, 'destroy']);
    Route::get('/authors/search', [AuthorController::class, 'search']);
    Route::get('/authors/suggestions', [AuthorController::class, 'suggestions']);

    // Recommendations
    Route::get('/recommendations/for-me', [RecommendationController::class, 'forUser']);
    Route::get('/recommendations/recently-viewed', [RecommendationController::class, 'recentlyViewed']);

    // Smart suggestions (authenticated)
    Route::get('/suggestions', [SmartSearchController::class, 'suggestions']);
    
    // Update user profile (program and interests)
    Route::put('/profile/preferences', [UserController::class, 'updatePreferences']);

    // Activity Logs
    Route::get('/activity-logs', [ActivityLogController::class, 'index']);
    Route::get('/activity-logs/{activityLog}', [ActivityLogController::class, 'show']);
    Route::get('/activity-logs/user/me', [ActivityLogController::class, 'userActivity']);
    Route::get('/activity-logs/export', [ActivityLogController::class, 'export']);

    // Plagiarism Checker
    Route::post('/plagiarism/check-text', [PlagiarismController::class, 'checkText']);
    Route::post('/theses/{thesis}/plagiarism-check', [PlagiarismController::class, 'checkThesis']);
    Route::get('/theses/{thesis}/plagiarism-checks', [PlagiarismController::class, 'getThesisChecks']);
    Route::get('/plagiarism-checks', [PlagiarismController::class, 'index']);
    Route::get('/plagiarism-checks/{check}', [PlagiarismController::class, 'getCheckDetail']);

    // User Favorites
    Route::post('/theses/{thesis}/favorite', function (Thesis $thesis) {
        request()->user()->favorites()->attach($thesis->id);
        return response()->json(['message' => 'Added to favorites']);
    });
    Route::delete('/theses/{thesis}/favorite', function (Thesis $thesis) {
        request()->user()->favorites()->detach($thesis->id);
        return response()->json(['message' => 'Removed from favorites']);
    });
    Route::get('/favorites', function () {
        $favorites = request()->user()->favorites()
            ->with(['category', 'uploader', 'activeDocument'])
            ->get();
        return response()->json($favorites);
    });
});

// Document download (can be accessed by authenticated or public users based on thesis status)
Route::get('/documents/{document}/download', [DocumentController::class, 'download'])
    ->name('documents.download');

// Document view (for inline PDF viewing)
Route::get('/documents/{document}/view', [DocumentController::class, 'view'])
    ->name('documents.view');

// Test upload endpoint
Route::post('/test-upload', [DocumentController::class, 'testUpload']);