<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentType extends Model
{
    use HasFactory;

    public const COLLECTIONS = [
        'produk_hukum' => 'Bank Produk Hukum',
        'perpustakaan' => 'Perpustakaan Digital',
        'edukasi' => 'Materi Edukasi',
    ];

    protected $fillable = [
        'name',
        'slug',
        'code_prefix',
        'review_interval_months',
        'collection',
        'description',
    ];

    public function scopeLibrary($query)
    {
        return $query->where('collection', 'perpustakaan');
    }

    public function isLibrary(): bool
    {
        return $this->collection === 'perpustakaan';
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }
}
