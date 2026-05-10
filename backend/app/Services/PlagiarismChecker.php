<?php

namespace App\Services;

use App\Models\Thesis;
use Illuminate\Support\Facades\Log;

class PlagiarismChecker
{
    /**
     * Check text for plagiarism against existing theses
     */
    public function checkSimilarity(string $text, ?int $excludeThesisId = null): array
    {
        try {
            // Get all approved theses except the one being checked
            $query = Thesis::approved();
            
            if ($excludeThesisId) {
                $query->where('id', '!=', $excludeThesisId);
            }
            
            $existingTheses = $query->get(['id', 'title', 'abstract', 'authors']);
            
            if ($existingTheses->isEmpty()) {
                return [
                    'overall_score' => 0,
                    'matches' => [],
                    'status' => 'completed',
                ];
            }

            $matches = [];
            $maxScore = 0;

            foreach ($existingTheses as $thesis) {
                // Combine title and abstract for comparison
                $thesisText = $thesis->title . ' ' . $thesis->abstract;
                
                // Calculate similarity
                $score = $this->calculateCosineSimilarity($text, $thesisText);
                
                if ($score > 15) { // Only include matches above 15%
                    $matches[] = [
                        'thesis_id' => $thesis->id,
                        'thesis_title' => $thesis->title,
                        'similarity_score' => round($score, 2),
                        'authors' => $thesis->authors,
                    ];
                    
                    $maxScore = max($maxScore, $score);
                }
            }

            // Sort matches by similarity score (highest first)
            usort($matches, function($a, $b) {
                return $b['similarity_score'] <=> $a['similarity_score'];
            });

            // Limit to top 10 matches
            $matches = array_slice($matches, 0, 10);

            return [
                'overall_score' => round($maxScore, 2),
                'matches' => $matches,
                'status' => 'completed',
            ];
        } catch (\Exception $e) {
            Log::error('Plagiarism check error: ' . $e->getMessage());
            return [
                'overall_score' => 0,
                'matches' => [],
                'status' => 'failed',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Calculate cosine similarity between two texts
     */
    private function calculateCosineSimilarity(string $text1, string $text2): float
    {
        // Normalize and tokenize
        $tokens1 = $this->tokenize($text1);
        $tokens2 = $this->tokenize($text2);

        if (empty($tokens1) || empty($tokens2)) {
            return 0;
        }

        // Create frequency vectors
        $vector1 = $this->createFrequencyVector($tokens1);
        $vector2 = $this->createFrequencyVector($tokens2);

        // Calculate cosine similarity
        $dotProduct = 0;
        $magnitude1 = 0;
        $magnitude2 = 0;

        $allKeys = array_unique(array_merge(array_keys($vector1), array_keys($vector2)));

        foreach ($allKeys as $key) {
            $val1 = $vector1[$key] ?? 0;
            $val2 = $vector2[$key] ?? 0;

            $dotProduct += $val1 * $val2;
            $magnitude1 += $val1 * $val1;
            $magnitude2 += $val2 * $val2;
        }

        $magnitude1 = sqrt($magnitude1);
        $magnitude2 = sqrt($magnitude2);

        if ($magnitude1 == 0 || $magnitude2 == 0) {
            return 0;
        }

        $similarity = $dotProduct / ($magnitude1 * $magnitude2);
        
        // Convert to percentage
        return $similarity * 100;
    }

    /**
     * Tokenize text into words
     */
    private function tokenize(string $text): array
    {
        // Convert to lowercase
        $text = strtolower($text);
        
        // Remove special characters and extra spaces
        $text = preg_replace('/[^a-z0-9\s]/', ' ', $text);
        $text = preg_replace('/\s+/', ' ', $text);
        
        // Split into words
        $words = explode(' ', trim($text));
        
        // Remove stop words and short words
        $stopWords = ['the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 
                      'of', 'with', 'by', 'from', 'as', 'is', 'was', 'are', 'were', 'be', 
                      'been', 'being', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 
                      'would', 'should', 'could', 'may', 'might', 'must', 'can', 'this', 
                      'that', 'these', 'those', 'it', 'its', 'they', 'them', 'their'];
        
        $words = array_filter($words, function($word) use ($stopWords) {
            return strlen($word) > 2 && !in_array($word, $stopWords);
        });
        
        return array_values($words);
    }

    /**
     * Create frequency vector from tokens
     */
    private function createFrequencyVector(array $tokens): array
    {
        $vector = [];
        foreach ($tokens as $token) {
            if (!isset($vector[$token])) {
                $vector[$token] = 0;
            }
            $vector[$token]++;
        }
        return $vector;
    }

    /**
     * Calculate Jaccard similarity (alternative method)
     */
    private function calculateJaccardSimilarity(string $text1, string $text2): float
    {
        $tokens1 = array_unique($this->tokenize($text1));
        $tokens2 = array_unique($this->tokenize($text2));

        if (empty($tokens1) || empty($tokens2)) {
            return 0;
        }

        $intersection = count(array_intersect($tokens1, $tokens2));
        $union = count(array_unique(array_merge($tokens1, $tokens2)));

        if ($union == 0) {
            return 0;
        }

        return ($intersection / $union) * 100;
    }
}
