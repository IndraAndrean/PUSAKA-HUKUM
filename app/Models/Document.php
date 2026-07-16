<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    use HasFactory;

    public const STATUS_LABELS = [
        'berlaku' => 'Berlaku',
        'diubah' => 'Diubah',
        'dicabut' => 'Dicabut',
        'tidak_berlaku' => 'Tidak Berlaku',
    ];

    public const ACCESS_LEVEL_LABELS = [
        'publik' => 'Publik',
        'internal' => 'Internal',
        'terbatas' => 'Terbatas',
    ];

    protected $fillable = [
        'document_code',
        'title',
        'author',
        'document_type_id',
        'document_number',
        'year',
        'enacted_date',
        'effective_date',
        'issuing_institution',
        'publisher',
        'isbn_issn',
        'edition_volume',
        'document_status',
        'legal_category_id',
        'bidang_subbidang',
        'keywords',
        'summary',
        'abstract',
        'legal_basis',
        'related_regulation',
        'document_version',
        'last_reviewed_at',
        'next_review_at',
        'access_level',
        'file_path',
        'uploaded_by',
        'views_count',
        'downloads_count',
    ];

    protected $casts = [
        'enacted_date' => 'date',
        'effective_date' => 'date',
        'last_reviewed_at' => 'date',
        'next_review_at' => 'date',
    ];

    protected $appends = [
        'metadata_completeness',
        'needs_review',
    ];

    public function type(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class, 'document_type_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(LegalCategory::class, 'legal_category_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function scopeVisibleFor(Builder $query, ?User $user): Builder
    {
        if (! $user) {
            return $query->where('access_level', 'publik');
        }

        if ($user->isAdmin()) {
            return $query;
        }

        return $query->whereIn('access_level', ['publik', 'internal']);
    }

    public function scopeSearch(Builder $query, string $keyword): Builder
    {
        $keyword = trim($keyword);

        if ($keyword === '') {
            return $query;
        }

        $matchingStatuses = $this->matchingKeys(self::STATUS_LABELS, $keyword);
        $matchingAccessLevels = $this->matchingKeys(self::ACCESS_LEVEL_LABELS, $keyword);
        $matchingCollections = $this->matchingKeys(DocumentType::COLLECTIONS, $keyword);

        return $query->where(function (Builder $inner) use (
            $keyword,
            $matchingStatuses,
            $matchingAccessLevels,
            $matchingCollections,
        ) {
            $inner->where('title', 'like', "%{$keyword}%")
                ->orWhere('author', 'like', "%{$keyword}%")
                ->orWhere('publisher', 'like', "%{$keyword}%")
                ->orWhere('isbn_issn', 'like', "%{$keyword}%")
                ->orWhere('edition_volume', 'like', "%{$keyword}%")
                ->orWhere('document_code', 'like', "%{$keyword}%")
                ->orWhere('document_number', 'like', "%{$keyword}%")
                ->orWhere('year', 'like', "%{$keyword}%")
                ->orWhere('issuing_institution', 'like', "%{$keyword}%")
                ->orWhere('bidang_subbidang', 'like', "%{$keyword}%")
                ->orWhere('keywords', 'like', "%{$keyword}%")
                ->orWhere('summary', 'like', "%{$keyword}%")
                ->orWhere('abstract', 'like', "%{$keyword}%")
                ->orWhere('legal_basis', 'like', "%{$keyword}%")
                ->orWhere('related_regulation', 'like', "%{$keyword}%")
                ->orWhereHas('type', function (Builder $type) use ($keyword, $matchingCollections) {
                    $type->where('name', 'like', "%{$keyword}%")
                        ->orWhere('code_prefix', 'like', "%{$keyword}%")
                        ->orWhere('description', 'like', "%{$keyword}%");

                    if ($matchingCollections !== []) {
                        $type->orWhereIn('collection', $matchingCollections);
                    }
                })
                ->orWhereHas('category', fn (Builder $category) => $category
                    ->where('name', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%"));

            if ($matchingStatuses !== []) {
                $inner->orWhereIn('document_status', $matchingStatuses);
            }

            if ($matchingAccessLevels !== []) {
                $inner->orWhereIn('access_level', $matchingAccessLevels);
            }
        });
    }

    public function getMetadataCompletenessAttribute(): int
    {
        if ($this->type?->isLibrary()) {
            $checks = [
                filled($this->document_code),
                filled($this->title),
                filled($this->author),
                filled($this->document_type_id),
                filled($this->year),
                filled($this->publisher),
                filled($this->legal_category_id),
                filled($this->bidang_subbidang),
                $this->keywordCount() >= 3,
                filled($this->summary),
                filled($this->document_version),
                filled($this->access_level),
                filled($this->uploaded_by),
                filled($this->created_at),
                filled($this->file_path),
            ];

            return (int) round((collect($checks)->filter()->count() / count($checks)) * 100);
        }

        $checks = [
            filled($this->document_code),
            filled($this->title),
            filled($this->document_type_id),
            filled($this->document_number),
            filled($this->year),
            filled($this->enacted_date),
            filled($this->effective_date),
            filled($this->issuing_institution),
            filled($this->document_status),
            filled($this->legal_category_id),
            $this->keywordCount() >= 3,
            filled($this->summary),
            filled($this->uploaded_by),
            filled($this->created_at),
            filled($this->file_path),
        ];

        return (int) round((collect($checks)->filter()->count() / count($checks)) * 100);
    }

    public function getNeedsReviewAttribute(): bool
    {
        return $this->next_review_at?->isPast() ?? false;
    }

    public function keywordCount(): int
    {
        return collect(preg_split('/[,;]+/', (string) $this->keywords))
            ->map(fn ($keyword) => trim($keyword))
            ->filter()
            ->unique(fn ($keyword) => mb_strtolower($keyword))
            ->count();
    }

    private function matchingKeys(array $labels, string $keyword): array
    {
        $normalizedKeyword = mb_strtolower(str_replace('_', ' ', $keyword));

        return collect($labels)
            ->filter(function (string $label, string $key) use ($normalizedKeyword) {
                return str_contains(mb_strtolower(str_replace('_', ' ', $key)), $normalizedKeyword)
                    || str_contains(mb_strtolower($label), $normalizedKeyword);
            })
            ->keys()
            ->all();
    }
}
