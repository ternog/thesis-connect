<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Thesis;
use App\Models\ThesisView;
use App\Models\ThesisDownload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SmartSearchController extends Controller
{
    /**
     * Smart search with personalized results
     */
    public function search(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:2',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $query = $request->input('query');
        $limit = $request->input('limit', 20);
        $user = $request->user();

        Log::info("Smart Search Query: {$query}");

        // Base search query
        $theses = Thesis::with(['category', 'authors', 'activeDocument'])
            ->approved()
            ->where(function ($q) use ($query) {
                // Search in title
                $q->where('title', 'LIKE', "%{$query}%")
                  // Search in abstract (case-insensitive)
                  ->orWhere('abstract', 'LIKE', "%{$query}%")
                  // Search in department
                  ->orWhere('department', 'LIKE', "%{$query}%")
                  // Search in program
                  ->orWhere('program', 'LIKE', "%{$query}%")
                  // Search in adviser
                  ->orWhere('adviser', 'LIKE', "%{$query}%");
                
                // Search in keywords (JSON array) - only if keywords exist
                try {
                    $q->orWhereRaw("JSON_SEARCH(keywords, 'one', ?) IS NOT NULL", ["%{$query}%"]);
                } catch (\Exception $e) {
                    Log::warning("Keywords search failed: " . $e->getMessage());
                }
                
                // Search in authors (JSON array - old format) - only if authors exist
                try {
                    $q->orWhereRaw("JSON_SEARCH(authors, 'one', ?) IS NOT NULL", ["%{$query}%"]);
                } catch (\Exception $e) {
                    Log::warning("Authors JSON search failed: " . $e->getMessage());
                }
                
                // Search in authors relationship (new format)
                $q->orWhereHas('authors', function($authorQuery) use ($query) {
                    $authorQuery->where('full_name', 'LIKE', "%{$query}%")
                                ->orWhere('first_name', 'LIKE', "%{$query}%")
                                ->orWhere('last_name', 'LIKE', "%{$query}%")
                                ->orWhere('email', 'LIKE', "%{$query}%");
                });
            });

        // Personalize results if user is logged in
        if ($user && $user->program) {
            $theses->addSelect([
                '*',
                DB::raw("CASE 
                    WHEN program = '{$user->program}' THEN 3
                    WHEN department = '{$user->department}' THEN 2
                    ELSE 1
                END as relevance_score")
            ])->orderBy('relevance_score', 'desc');
        }

        $theses->orderBy('view_count', 'desc')
               ->orderBy('download_count', 'desc')
               ->orderBy('created_at', 'desc');

        $results = $theses->limit($limit)->get();

        // Log the SQL query for debugging
        Log::info("Search SQL: " . $theses->toSql());
        Log::info("Search Bindings: " . json_encode($theses->getBindings()));
        Log::info("Search Results Count: " . $results->count());

        return response()->json([
            'results' => $results,
            'count' => $results->count(),
            'personalized' => $user && $user->program ? true : false,
        ]);
    }

    /**
     * Get personalized suggestions for the user
     */
    public function suggestions(Request $request)
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'message' => 'Authentication required for personalized suggestions',
                ], 401);
            }

            Log::info('Fetching suggestions for user: ' . $user->id);

            $suggestions = [];
            $viewedThesisIds = [];

            // 1. Based on user's program
            if ($user->program) {
                try {
                    $programTheses = Thesis::with(['category', 'authors', 'activeDocument'])
                        ->approved()
                        ->where('program', $user->program)
                        ->orderBy('view_count', 'desc')
                        ->orderBy('download_count', 'desc')
                        ->limit(5)
                        ->get();

                    if ($programTheses->count() > 0) {
                        $suggestions['program_based'] = [
                            'title' => "Popular in {$user->program}",
                            'description' => "Most viewed theses in your program",
                            'theses' => $programTheses,
                        ];
                    }
                } catch (\Exception $e) {
                    Log::error('Program-based suggestions error: ' . $e->getMessage());
                }
            }

            // 2. Based on user's interests
            if ($user->interests && is_array($user->interests) && count($user->interests) > 0) {
                try {
                    $interestTheses = Thesis::with(['category', 'authors', 'activeDocument'])
                        ->approved()
                        ->where(function($q) use ($user) {
                            foreach ($user->interests as $interest) {
                                $q->orWhere('title', 'LIKE', "%{$interest}%")
                                  ->orWhere('abstract', 'LIKE', "%{$interest}%");
                            }
                        })
                        ->orderBy('view_count', 'desc')
                        ->limit(5)
                        ->get();

                    if ($interestTheses->count() > 0) {
                        $suggestions['interest_based'] = [
                            'title' => 'Based on Your Interests',
                            'description' => 'Theses matching your research interests',
                            'theses' => $interestTheses,
                        ];
                    }
                } catch (\Exception $e) {
                    Log::error('Interest-based suggestions error: ' . $e->getMessage());
                }
            }

            // 3. Based on user's viewing history
            try {
                $viewedThesisIds = ThesisView::where('user_id', $user->id)
                    ->distinct()
                    ->pluck('thesis_id')
                    ->toArray();

                if (count($viewedThesisIds) > 0) {
                    // Get keywords from viewed theses
                    $viewedKeywords = Thesis::whereIn('id', $viewedThesisIds)
                        ->pluck('keywords')
                        ->flatten()
                        ->unique()
                        ->take(10)
                        ->toArray();

                    if (count($viewedKeywords) > 0) {
                        $similarTheses = Thesis::with(['category', 'authors', 'activeDocument'])
                            ->approved()
                            ->whereNotIn('id', $viewedThesisIds)
                            ->where(function($q) use ($viewedKeywords) {
                                foreach ($viewedKeywords as $keyword) {
                                    $q->orWhere('title', 'LIKE', "%{$keyword}%")
                                      ->orWhere('abstract', 'LIKE', "%{$keyword}%");
                                }
                            })
                            ->orderBy('view_count', 'desc')
                            ->limit(5)
                            ->get();

                        if ($similarTheses->count() > 0) {
                            $suggestions['similar_to_viewed'] = [
                                'title' => 'Similar to What You\'ve Viewed',
                                'description' => 'Based on your reading history',
                                'theses' => $similarTheses,
                            ];
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error('Viewing history suggestions error: ' . $e->getMessage());
            }

            // 4. Based on user's department
            if ($user->department) {
                try {
                    $departmentTheses = Thesis::with(['category', 'authors', 'activeDocument'])
                        ->approved()
                        ->where('department', $user->department)
                        ->whereNotIn('id', $viewedThesisIds)
                        ->orderBy('created_at', 'desc')
                        ->limit(5)
                        ->get();

                    if ($departmentTheses->count() > 0) {
                        $suggestions['department_based'] = [
                            'title' => "Recent in {$user->department}",
                            'description' => 'Latest theses from your department',
                            'theses' => $departmentTheses,
                        ];
                    }
                } catch (\Exception $e) {
                    Log::error('Department-based suggestions error: ' . $e->getMessage());
                }
            }

            // 5. Trending theses (most viewed in last 30 days) - Simplified
            try {
                $trendingTheses = Thesis::with(['category', 'authors', 'activeDocument'])
                    ->approved()
                    ->orderBy('view_count', 'desc')
                    ->orderBy('download_count', 'desc')
                    ->limit(5)
                    ->get();

                if ($trendingTheses->count() > 0) {
                    $suggestions['trending'] = [
                        'title' => 'Trending Now',
                        'description' => 'Most popular theses',
                        'theses' => $trendingTheses,
                    ];
                }
            } catch (\Exception $e) {
                Log::error('Trending suggestions error: ' . $e->getMessage());
            }

            // 6. Recently added
            try {
                $recentTheses = Thesis::with(['category', 'authors', 'activeDocument'])
                    ->approved()
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get();

                if ($recentTheses->count() > 0) {
                    $suggestions['recent'] = [
                        'title' => 'Recently Added',
                        'description' => 'Latest additions to the library',
                        'theses' => $recentTheses,
                    ];
                }
            } catch (\Exception $e) {
                Log::error('Recent suggestions error: ' . $e->getMessage());
            }

            Log::info('Suggestions fetched successfully. Count: ' . count($suggestions));

            return response()->json([
                'suggestions' => $suggestions,
                'user_profile' => [
                    'program' => $user->program,
                    'department' => $user->department,
                    'interests' => $user->interests,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Suggestions error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'message' => 'Failed to load suggestions',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred',
                'suggestions' => [],
                'user_profile' => [
                    'program' => null,
                    'department' => null,
                    'interests' => [],
                ],
            ], 500);
        }
    }

    /**
     * Get autocomplete suggestions
     */
    public function autocomplete(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:2',
            'limit' => 'nullable|integer|min:1|max:20',
        ]);

        $query = $request->input('query');
        $limit = $request->input('limit', 10);

        $suggestions = [];

        // Title suggestions
        $titles = Thesis::approved()
            ->where('title', 'LIKE', "%{$query}%")
            ->limit($limit)
            ->pluck('title')
            ->map(fn($title) => ['type' => 'title', 'value' => $title]);

        $suggestions = array_merge($suggestions, $titles->toArray());

        // Author suggestions
        $authors = DB::table('authors')
            ->where(function($q) use ($query) {
                $q->where('full_name', 'LIKE', "%{$query}%")
                  ->orWhere('first_name', 'LIKE', "%{$query}%")
                  ->orWhere('last_name', 'LIKE', "%{$query}%");
            })
            ->limit($limit)
            ->pluck('full_name')
            ->map(fn($name) => ['type' => 'author', 'value' => $name]);

        $suggestions = array_merge($suggestions, $authors->toArray());

        // Keyword suggestions
        $keywords = Thesis::approved()
            ->get()
            ->pluck('keywords')
            ->flatten()
            ->filter(fn($keyword) => stripos($keyword, $query) !== false)
            ->unique()
            ->take($limit)
            ->map(fn($keyword) => ['type' => 'keyword', 'value' => $keyword]);

        $suggestions = array_merge($suggestions, $keywords->toArray());

        // Program suggestions
        $programs = Thesis::approved()
            ->where('program', 'LIKE', "%{$query}%")
            ->distinct()
            ->limit($limit)
            ->pluck('program')
            ->map(fn($program) => ['type' => 'program', 'value' => $program]);

        $suggestions = array_merge($suggestions, $programs->toArray());

        return response()->json([
            'suggestions' => array_slice($suggestions, 0, $limit),
        ]);
    }
}
