<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Consultation extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'tracking_code', 'name', 'email', 'question', 'answer', 'status', 'answered_by', 'answered_at'];

    protected $casts = [
        'answered_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Consultation $consultation) {
            if (blank($consultation->tracking_code)) {
                $consultation->tracking_code = static::generateTrackingCode();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function answerer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'answered_by');
    }

    /**
     * Alfabet sengaja tanpa 0/O/1/I/L supaya kode mudah dibaca dan diketik ulang
     * oleh warga saat mengecek status konsultasi.
     */
    private static function generateTrackingCode(): string
    {
        $alphabet = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

        do {
            $code = 'KH-'.collect(range(1, 6))
                ->map(fn () => $alphabet[random_int(0, strlen($alphabet) - 1)])
                ->implode('');
        } while (static::where('tracking_code', $code)->exists());

        return $code;
    }
}
