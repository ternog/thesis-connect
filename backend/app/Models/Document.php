<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'thesis_id',
        'original_name',
        'file_path',
        'file_hash',
        'file_size',
        'mime_type',
        'version',
        'is_active',
        'uploaded_by'
    ];

    protected $casts = [
        'file_size' => 'integer',
        'version' => 'integer',
        'is_active' => 'boolean'
    ];

    public function thesis(): BelongsTo
    {
        return $this->belongsTo(Thesis::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getFileSizeHumanAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getDownloadUrlAttribute(): string
    {
        return route('documents.download', $this->id);
    }

    public function exists(): bool
    {
        $fullPath = storage_path('app/public/' . $this->file_path);
        return file_exists($fullPath);
    }

    public function delete(): bool
    {
        // Delete physical file using pure PHP
        $fullPath = storage_path('app/public/' . $this->file_path);
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
        
        return parent::delete();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}