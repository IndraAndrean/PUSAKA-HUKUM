<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpi_targets', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('documents_target')->default(300);
            $table->unsignedInteger('legislation_target')->default(200);
            $table->unsignedInteger('internal_documents_target')->default(100);
            $table->unsignedInteger('legal_studies_target')->default(30);
            $table->unsignedInteger('education_materials_target')->default(10);
            $table->unsignedInteger('registered_users_target')->default(100);
            $table->unsignedInteger('accesses_target')->default(1000);
            $table->decimal('satisfaction_target_percent', 5, 2)->default(80);
            $table->decimal('utilization_target_percent', 5, 2)->default(75);
            $table->unsignedInteger('search_time_target_seconds')->default(180);
            $table->decimal('satker_coverage_target_percent', 5, 2)->default(100);
            $table->decimal('polres_coverage_target_percent', 5, 2)->default(100);
            $table->decimal('satker_coverage_percent', 5, 2)->default(0);
            $table->decimal('polres_coverage_percent', 5, 2)->default(0);
            $table->unsignedSmallInteger('appointed_admin_count')->default(0);
            $table->boolean('sop_available')->default(false);
            $table->boolean('user_guide_available')->default(false);
            $table->text('verification_notes')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_targets');
    }
};
