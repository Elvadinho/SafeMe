<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lost & found documents (CNI, passport, diplomas...).
 *  - found_documents   : "I found a document"   (posted by the finder)
 *  - lost_declarations : "I lost my document"   (posted by the owner)
 *  - document_matches  : the app links the two automatically, an admin confirms,
 *                        then both sides see each other's contact.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('found_documents', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();

            $table->string('doc_type', 30);
            $table->string('owner_name', 120);          // name written on the document
            $table->string('owner_name_key', 160);      // normalized name, used for matching
            $table->string('number_last_digits', 4)->nullable(); // only the last digits, never the full number
            $table->string('found_city', 80);
            $table->string('found_place', 160)->nullable();
            $table->date('found_at');

            $table->foreignId('finder_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('finder_name', 120);
            $table->string('finder_phone', 16);         // NEVER public

            $table->string('status', 20)->default('available');
            $table->timestampsTz();

            $table->index(['doc_type', 'owner_name_key']);
            $table->index('status');
        });

        Schema::create('lost_declarations', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();

            $table->string('doc_type', 30);
            $table->string('owner_name', 120);
            $table->string('owner_name_key', 160);
            $table->string('number_last_digits', 4)->nullable();
            $table->string('lost_city', 80)->nullable();
            $table->string('owner_phone', 16);          // NEVER public

            $table->string('status', 20)->default('open');
            $table->timestampsTz();

            $table->index(['doc_type', 'owner_name_key']);
            $table->index(['owner_phone', 'created_at']);
        });

        Schema::create('document_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('found_document_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lost_declaration_id')->constrained()->cascadeOnDelete();
            $table->string('status', 20)->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('reviewed_at')->nullable();
            $table->timestampsTz();

            // The same pair can only be matched once.
            $table->unique(['found_document_id', 'lost_declaration_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_matches');
        Schema::dropIfExists('lost_declarations');
        Schema::dropIfExists('found_documents');
    }
};
