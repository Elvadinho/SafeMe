<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('missing_reports', function (Blueprint $table) {
            $table->id();
            // Public id used in URLs: /alerts/9f1c...  Sequential ids (1, 2, 3)
            // would let anyone count reports or guess the next one.
            $table->uuid('public_id')->unique();

            // ── The child ───────────────────────────────
            $table->string('child_name', 120);
            $table->unsignedTinyInteger('child_age');          // approximate age, 0-17
            $table->string('child_gender', 10)->nullable();    // 'male' | 'female'
            $table->string('child_photo_path');                // public disk, shown after approval
            $table->text('description')->nullable();           // clothes, marks, context

            // ── The disappearance ───────────────────────
            $table->timestampTz('last_seen_at');
            // decimal, not float: float rounds (3.8480000001), decimal(10,7) is exact to ~1 cm
            $table->decimal('last_seen_lat', 10, 7);
            $table->decimal('last_seen_lng', 10, 7);
            $table->string('last_seen_place', 160)->nullable(); // "Marché Mokolo, Yaoundé"

            // ── The reporter (no account needed) ────────
            $table->string('reporter_name', 120);
            $table->string('reporter_phone', 16);              // E.164; NEVER shown publicly
            $table->string('relationship', 20);                // Relationship enum

            // ── Moderation ──────────────────────────────
            $table->string('status', 20)->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('reviewed_at')->nullable();
            $table->string('rejection_reason', 255)->nullable();
            $table->timestampTz('resolved_at')->nullable();    // found or closed

            $table->timestampsTz();
            $table->softDeletes(); // a deleted report can be recovered (police evidence)

            // The public map query: "approved reports, newest first"
            $table->index(['status', 'last_seen_at']);
            // Anti-spam: "how many reports did this phone send recently?"
            $table->index(['reporter_phone', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('missing_reports');
    }
};
