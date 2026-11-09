<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sightings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('missing_report_id')->constrained()->cascadeOnDelete();
            // Citizen may be logged in (user_id) or anonymous (phone only)
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('witness_phone', 16)->nullable();

            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);
            $table->timestampTz('seen_at');
            $table->text('note')->nullable();
            $table->string('photo_path')->nullable(); // private disk until confirmed

            // Only CONFIRMED sightings move the public search zone (anti-troll)
            $table->string('status', 20)->default('pending');
            $table->timestampsTz();

            $table->index(['missing_report_id', 'status', 'seen_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sightings');
    }
};
