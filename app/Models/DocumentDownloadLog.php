<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentDownloadLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['user_id', 'document_id', 'ip_address', 'downloaded_at'];

    protected $casts = [
        'downloaded_at' => 'datetime',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
