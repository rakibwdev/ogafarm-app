<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Jobs & markets: postings, applications, market directory, price contributions.
 * Spec ref: S11, S12; open decision — consultant as profile specialization.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_postings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employer_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type', 24)->default('full_time'); // full_time|seasonal|consultant_task
            $table->string('location')->nullable();
            $table->text('qualifications')->nullable();
            $table->string('compensation')->nullable();
            $table->date('application_deadline')->nullable();
            $table->boolean('published')->default(true);
            $table->string('moderation_status', 24)->default('approved');
            $table->timestampsTz();
            $table->softDeletes();

            $table->index(['published', 'moderation_status']);
        });

        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('job_postings')->cascadeOnDelete();
            $table->foreignId('applicant_id')->constrained('users')->cascadeOnDelete();
            $table->text('cover_message')->nullable();
            $table->string('portfolio_path')->nullable();
            $table->string('status', 24)->default('submitted'); // draft|submitted|reviewed|shortlisted|rejected|hired
            $table->timestampsTz();

            $table->unique(['job_id', 'applicant_id']); // U11: duplicate application blocker
        });

        Schema::create('markets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('locality');
            $table->string('operating_days')->nullable();
            $table->string('contact')->nullable();
            $table->foreignId('contributed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('moderation_status', 24)->default('pending'); // pending|approved|rejected
            $table->timestampsTz();

            $table->index(['locality', 'moderation_status']);
        });

        Schema::create('market_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('market_id')->constrained()->cascadeOnDelete();
            $table->string('commodity', 64);
            $table->string('unit', 24)->default('kg');
            $table->decimal('price', 12, 2);
            $table->foreignId('contributed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('verification_status', 24)->default('unverified'); // unverified|verified|flagged
            $table->date('price_date');
            $table->timestampsTz();

            $table->index(['market_id', 'commodity', 'price_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('market_prices');
        Schema::dropIfExists('markets');
        Schema::dropIfExists('applications');
        Schema::dropIfExists('job_postings');
    }
};
