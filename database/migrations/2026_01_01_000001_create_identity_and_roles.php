<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Identity layer: users extension, multi-role assignments, KYC verification.
 * Spec ref: project_documentation.md §9 (users, user_roles, verifications)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone_number', 32)->nullable()->unique()->after('email');
            $table->string('referral_code', 16)->nullable()->unique()->after('phone_number');
            $table->boolean('is_identity_verified')->default(false)->after('referral_code');
            $table->string('locale', 8)->default('en')->after('is_identity_verified');
            $table->softDeletes()->after('remember_token');
        });

        // A user may hold multiple authorized roles (server-enforced).
        Schema::create('user_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role_name', 32); // farmer|supplier|buyer|group_buyer|diaspora_buyer|transporter|job_seeker|consultant|admin
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();

            $table->unique(['user_id', 'role_name']);
            $table->index('role_name');
        });

        // KYC / business verification documents (private, admin-reviewed).
        Schema::create('verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 32); // identity_doc_1|identity_doc_2|business_reg|farm_evidence|vehicle_doc|credential
            $table->string('document_path')->nullable(); // object storage key, never public
            $table->string('status', 24)->default('pending'); // pending|approved|rejected
            $table->text('review_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('reviewed_at')->nullable();
            $table->timestampsTz();

            $table->index(['user_id', 'type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verifications');
        Schema::dropIfExists('user_roles');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone_number', 'referral_code', 'is_identity_verified', 'locale', 'deleted_at']);
        });
    }
};
