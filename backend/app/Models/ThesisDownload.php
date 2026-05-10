<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThesisDownload extends Model
{
    use HasFactory;

    protected $fillable = [
        'thesis_id',
        'user_id',
        'document_id',
        'ip_address',
        'user_agent',
    ];

    public function thesis(): BelongsTo
    {
        return $this->belongsTo(Thesis::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public static function recordDownload(Thesis $thesis, Document $document, ?User $user = null)
    {
        return static::create([
            'thesis_id' => $thesis->id,
            'document_id' => $document->id,
            'user_id' => $user?->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
