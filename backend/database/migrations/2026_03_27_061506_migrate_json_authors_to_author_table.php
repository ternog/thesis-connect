<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Thesis;
use App\Models\Author;

return new class extends Migration
{
    public function up(): void
    {
        // Migrate existing JSON authors to the authors table and pivot table
        $theses = Thesis::whereNotNull('authors')->get();
        
        foreach ($theses as $thesis) {
            $authorsJson = $thesis->getRawOriginal('authors');
            
            if (!$authorsJson) {
                continue;
            }
            
            $authorNames = json_decode($authorsJson, true);
            
            if (!is_array($authorNames) || empty($authorNames)) {
                continue;
            }
            
            $authorIds = [];
            
            foreach ($authorNames as $index => $authorName) {
                if (empty($authorName)) {
                    continue;
                }
                
                // Parse name - assume format is either "FirstName" or "LastName, FirstName M."
                $nameParts = $this->parseName($authorName);
                
                // Create or find author
                $author = Author::firstOrCreate(
                    [
                        'last_name' => $nameParts['last_name'],
                        'first_name' => $nameParts['first_name'],
                        'middle_initial' => $nameParts['middle_initial'],
                    ],
                    [
                        'author_type' => 'student',
                    ]
                );
                
                $authorIds[$author->id] = ['order' => $index + 1];
            }
            
            // Attach authors to thesis
            if (!empty($authorIds)) {
                $thesis->authors()->sync($authorIds);
            }
        }
    }
    
    private function parseName(string $name): array
    {
        $name = trim($name);
        
        // Check if name contains comma (format: "LastName, FirstName M.")
        if (strpos($name, ',') !== false) {
            $parts = explode(',', $name, 2);
            $lastName = trim($parts[0]);
            $firstPart = trim($parts[1]);
            
            // Check for middle initial
            $nameParts = explode(' ', $firstPart);
            $firstName = $nameParts[0];
            $middleInitial = isset($nameParts[1]) ? rtrim($nameParts[1], '.') : null;
            
            return [
                'last_name' => $lastName,
                'first_name' => $firstName,
                'middle_initial' => $middleInitial,
            ];
        }
        
        // Simple name format - treat as first name, use same as last name
        return [
            'last_name' => $name,
            'first_name' => $name,
            'middle_initial' => null,
        ];
    }

    public function down(): void
    {
        // Remove all author relationships
        \DB::table('author_thesis')->truncate();
        
        // Optionally remove all authors
        Author::truncate();
    }
};
