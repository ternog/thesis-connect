<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Author extends Model
{
    use HasFactory;

    protected $fillable = [
        'last_name',
        'first_name',
        'middle_initial',
        'full_name',
        'email',
        'department',
        'author_type',
        'thesis_count'
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($author) {
            $author->full_name = $author->getFormattedName();
        });
    }

    public function theses(): BelongsToMany
    {
        return $this->belongsToMany(Thesis::class, 'author_thesis')
            ->withPivot('order')
            ->withTimestamps()
            ->orderBy('author_thesis.order');
    }

    public function getFormattedName(): string
    {
        $name = "{$this->last_name}, {$this->first_name}";
        if ($this->middle_initial) {
            $name .= " {$this->middle_initial}.";
        }
        return $name;
    }

    public static function findOrCreate(string $lastName, string $firstName, ?string $middleInitial = null): self
    {
        return static::firstOrCreate(
            [
                'last_name' => $lastName,
                'first_name' => $firstName,
                'middle_initial' => $middleInitial,
            ],
            [
                'author_type' => 'student',
            ]
        );
    }

    public function incrementThesisCount()
    {
        $this->increment('thesis_count');
    }

    public function decrementThesisCount()
    {
        $this->decrement('thesis_count');
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('full_name', 'LIKE', "%{$search}%")
              ->orWhere('last_name', 'LIKE', "%{$search}%")
              ->orWhere('first_name', 'LIKE', "%{$search}%");
        });
    }

    public function scopeByType($query, $type)
    {
        return $query->where('author_type', $type);
    }
}
