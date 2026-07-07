<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('satisfaction_surveys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('respondent_key', 64)->unique();
            $table->string('respondent_type', 40);
            $table->unsignedTinyInteger('accessibility_rating');
            $table->unsignedTinyInteger('speed_rating');
            $table->unsignedTinyInteger('content_rating');
            $table->unsignedTinyInteger('ease_rating');
            $table->unsignedTinyInteger('overall_rating');
            $table->boolean('found_document');
            $table->unsignedInteger('search_duration_seconds')->nullable();
            $table->string('most_useful_feature', 30);
            $table->text('feedback')->nullable();
            $table->string('ip_hash', 64)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamps();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('satisfaction_surveys');
    }
};
