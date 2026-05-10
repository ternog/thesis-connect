<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Thesis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecommendationController extends Controller
{
    public function forUser(Request $request)
    {
        $user = $request->user();
        $recommendations = collect();

        // 1. Based on user's program
        if ($user->program) {
            $programTheses = Thesis::approved()
                ->where('program', $user->program)
                ->with(['category', 'uploader', 'activeDocument'])
                ->orderBy('view_count', 'desc')
                ->limit(5)
                ->get();
            
            $recommendations = $recommendations->merge($programTheses);
        }

        // 2. Based on user's interests
        if ($user->interests && is_array($user->interests)) {
            foreach ($user->interests as $interest) {
                $interestTheses = Thesis::approved()
                    ->where(function ($query) use ($interest) {
                        $query->where('title', 'LIKE', "%{$interest}%")
                              ->orWhere('abstract', 'LIKE', "%{$interest}%")
                              ->orWhereJsonContains('keywords', $interest);
                    })
                    ->with(['category', 'uploader', 'activeDocument'])
                    ->orderBy('download_count', 'desc')
                    ->limit(3)
                    ->get();
                
                $recommendations = $recommendations->merge($interestTheses);
            }
        }

        // 3. Popular theses in user's department
        if ($user->department) {
            $deptTheses = Thesis::approved()
                ->where('department', $user->department)
                ->with(['category', 'uploader', 'activeDocument'])
                ->orderBy('download_count', 'desc')
                ->limit(5)
                ->get();
            
            $recommendations = $recommendations->merge($deptTheses);
        }

        // 4. Recently viewed by similar users
        $similarUserTheses = Thesis::approved()
            ->whereHas('views', function ($query) use ($user) {
                $query->whereIn('user_id', function ($subQuery) use ($user) {
                    $subQuery->select('user_id')
                        ->from('thesis_views')
                        ->where('user_id', '!=', $user->id)
                        ->whereIn('thesis_id', function ($innerQuery) use ($user) {
                            $innerQuery->select('thesis_id')
                                ->from('thesis_views')
                                ->where('user_id', $user->id);
                        });
                });
            })
            ->with(['category', 'uploader', 'activeDocument'])
            ->orderBy('view_count', 'desc')
            ->limit(5)
            ->get();
        
        $recommendations = $recommendations->merge($similarUserTheses);

        // Remove duplicates and limit to 15
        $recommendations = $recommendations->unique('id')->take(15)->values();

        return response()->json($recommendations);
    }

    public function trending(Request $request)
    {
        $days = $request->get('days', 7);

        $trending = Thesis::approved()
            ->where('last_viewed_at', '>=', now()->subDays($days))
            ->with(['category', 'uploader', 'activeDocument'])
            ->orderBy('view_count', 'desc')
            ->limit(10)
            ->get();

        return response()->json($trending);
    }

    public function popular(Request $request)
    {
        $popular = Thesis::approved()
            ->with(['category', 'uploader', 'activeDocument'])
            ->orderBy('download_count', 'desc')
            ->orderBy('view_count', 'desc')
            ->limit(10)
            ->get();

        return response()->json($popular);
    }

    public function related(Thesis $thesis)
    {
        // Find related theses based on keywords, category, and program
        $related = Thesis::approved()
            ->where('id', '!=', $thesis->id)
            ->where(function ($query) use ($thesis) {
                // Same category
                if ($thesis->category_id) {
                    $query->orWhere('category_id', $thesis->category_id);
                }
                
                // Same program
                $query->orWhere('program', $thesis->program);
                
                // Shared keywords
                if ($thesis->keywords && is_array($thesis->keywords)) {
                    foreach ($thesis->keywords as $keyword) {
                        $query->orWhereJsonContains('keywords', $keyword);
                    }
                }
            })
            ->with(['category', 'uploader', 'activeDocument'])
            ->orderBy('view_count', 'desc')
            ->limit(6)
            ->get();

        return response()->json($related);
    }

    public function recentlyViewed(Request $request)
    {
        $user = $request->user();

        $recentlyViewed = Thesis::approved()
            ->whereHas('views', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with(['category', 'uploader', 'activeDocument'])
            ->orderByDesc(function ($query) use ($user) {
                $query->select('created_at')
                    ->from('thesis_views')
                    ->whereColumn('thesis_id', 'theses.id')
                    ->where('user_id', $user->id)
                    ->latest()
                    ->limit(1);
            })
            ->limit(10)
            ->get();

        return response()->json($recentlyViewed);
    }
}
