<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlagiarismCheck extends Model
{
    use HasFactory;

    protected $fillable = [
        'thesis_id',
        'checked_by',
        'similarity_score',
        'matches',
        'checked_content',
        'status',
        'notes',
    ];

    protected $casts = [
        'matches' => 'array',
        'similarity_score' => 'decimal:2',
    ];

    public function thesis(): BelongsTo
    {
        return $this->belongsTo(Thesis::class);
    }

    public function checker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }

    public function getSeverityLevel(): string
    {
        if ($this->similarity_score >= 70) {
            return 'high';
        } elseif ($this->similarity_score >= 40) {
            return 'medium';
        } elseif ($this->similarity_score >= 20) {
            return 'low';
        }
        return 'minimal';
    }

    public function getSeverityColor(): string
    {
        $level = $this->getSeverityLevel();
        return match($level) {
            'high' => '#d32f2f',
            'medium' => '#f57c00',
            'low' => '#fbc02d',
            'minimal' => '#388e3c',
            default => '#757575',
        };
    }
}
