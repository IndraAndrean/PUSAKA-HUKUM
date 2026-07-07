<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Backup extends Model
{
    use HasFactory;

    protected $fillable = [
        'created_by',
        'restored_by',
        'filename',
        'disk_path',
        'type',
        'status',
        'size_bytes',
        'database_size_bytes',
        'documents_count',
        'checksum_sha256',
        'error_message',
        'restored_at',
    ];

    protected $casts = [
        'restored_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function restorer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restored_by');
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = (float) $this->size_bytes;
        $units = ['B', 'KB', 'MB', 'GB'];
        $unit = 0;

        while ($bytes >= 1024 && $unit < count($units) - 1) {
            $bytes /= 1024;
            $unit++;
        }

        return number_format($bytes, $unit === 0 ? 0 : 2, ',', '.').' '.$units[$unit];
    }
}
