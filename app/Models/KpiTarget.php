<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;

class KpiTarget extends Model
{
    protected $attributes = [
        'documents_target' => 300,
        'legislation_target' => 200,
        'internal_documents_target' => 100,
        'legal_studies_target' => 30,
        'education_materials_target' => 10,
        'registered_users_target' => 100,
        'accesses_target' => 1000,
        'satisfaction_target_percent' => 80,
        'utilization_target_percent' => 75,
        'search_time_target_seconds' => 180,
        'satker_coverage_target_percent' => 100,
        'polres_coverage_target_percent' => 100,
        'satker_coverage_percent' => 0,
        'polres_coverage_percent' => 0,
        'appointed_admin_count' => 0,
        'sop_available' => false,
        'user_guide_available' => false,
    ];

    protected $fillable = [
        'documents_target',
        'legislation_target',
        'internal_documents_target',
        'legal_studies_target',
        'education_materials_target',
        'registered_users_target',
        'accesses_target',
        'satisfaction_target_percent',
        'utilization_target_percent',
        'search_time_target_seconds',
        'satker_coverage_target_percent',
        'polres_coverage_target_percent',
        'satker_coverage_percent',
        'polres_coverage_percent',
        'appointed_admin_count',
        'sop_available',
        'user_guide_available',
        'verification_notes',
        'updated_by',
    ];

    protected $casts = [
        'satisfaction_target_percent' => 'float',
        'utilization_target_percent' => 'float',
        'satker_coverage_target_percent' => 'float',
        'polres_coverage_target_percent' => 'float',
        'satker_coverage_percent' => 'float',
        'polres_coverage_percent' => 'float',
        'sop_available' => 'boolean',
        'user_guide_available' => 'boolean',
    ];

    public static function current(): self
    {
        if (! Schema::hasTable('kpi_targets')) {
            return new self;
        }

        return static::query()->firstOrCreate([]);
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
