<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentImportBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'imported_by',
        'spreadsheet_name',
        'pdf_archive_name',
        'total_rows',
        'successful_rows',
        'failed_rows',
        'status',
        'results',
    ];

    protected $casts = [
        'results' => 'array',
    ];

    public function importer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'imported_by');
    }
}
