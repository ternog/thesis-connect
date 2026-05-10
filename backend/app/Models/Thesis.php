<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Thesis extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'authors',
        'adviser',
        'year',
        'department',
        'program',
        'academic_level',
        'document_type',
        'abstract',
        'keywords',
        'category_id',
        'uploaded_by',
        'status',
        'download_count',
        'approved_at',
        'approved_by'
    ];

    protected $casts = [
        'authors' => 'array',
        'keywords' => 'array',
        'approved_at' => 'datetime',
        'year' => 'integer',
        'download_count' => 'integer'
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function activeDocument()
    {
        return $this->hasOne(Document::class)->where('is_active', true);
    }

    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(Author::class, 'author_thesis')
            ->withPivot('order')
            ->withTimestamps()
            ->orderBy('author_thesis.order');
    }

    public function views(): HasMany
    {
        return $this->hasMany(ThesisView::class);
    }

    public function downloads(): HasMany
    {
        return $this->hasMany(ThesisDownload::class);
    }

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_favorites')
            ->withTimestamps();
    }

    public function plagiarismChecks(): HasMany
    {
        return $this->hasMany(PlagiarismCheck::class);
    }

    public function latestPlagiarismCheck()
    {
        return $this->hasOne(PlagiarismCheck::class)->latestOfMany();
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeByYear($query, $year)
    {
        return $query->where('year', $year);
    }

    public function scopeByDepartment($query, $department)
    {
        return $query->where('department', $department);
    }

    public function scopeByProgram($query, $program)
    {
        return $query->where('program', $program);
    }

    public function scopeByAcademicLevel($query, $level)
    {
        return $query->where('academic_level', $level);
    }

    public function scopeByDocumentType($query, $type)
    {
        return $query->where('document_type', $type);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'LIKE', "%{$search}%")
              ->orWhere('abstract', 'LIKE', "%{$search}%")
              ->orWhereJsonContains('authors', $search)
              ->orWhereJsonContains('keywords', $search);
        });
    }

    public function incrementDownloadCount()
    {
        $this->increment('download_count');
    }

    // Status constants
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_ARCHIVED = 'archived';

    // Academic level constants
    public const LEVEL_UNDERGRADUATE = 'undergraduate';
    public const LEVEL_GRADUATE = 'graduate';

    // Document type constants
    public const TYPE_STUDENT_THESIS = 'student_thesis';
    public const TYPE_FACULTY_RESEARCH = 'faculty_research';
}