<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThesisView extends Model
{
    use HasFactory;

    protected $fillable = [
        'thesis_id',
        'user_id',
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

    public static function recordView(Thesis $thesis, ?User $user = null)
    {
        // Check if this user/IP already viewed this thesis today
        $existingView = static::where('thesis_id', $thesis->id)
            ->where(function ($query) use ($user) {
                if ($user) {
                    $query->where('user_id', $user->id);
                } else {
                    $query->where('ip_address', request()->ip())
                          ->whereNull('user_id');
                }
            })
            ->whereDate('created_at', today())
            ->first();

        // Only record new view if not viewed today
        if (!$existingView) {
            $thesis->increment('view_count');
            $thesis->update(['last_viewed_at' => now()]);

            return static::create([
                'thesis_id' => $thesis->id,
                'user_id' => $user?->id,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }

        return $existingView;
    }
}
