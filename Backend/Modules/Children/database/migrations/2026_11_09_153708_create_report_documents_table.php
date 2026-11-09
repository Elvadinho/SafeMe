<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A separate table because one report has SEVERAL proof files
        // (birth certificate + CNI + maybe a photo). Columns can't hold a list.
        Schema::create('report_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('missing_report_id')->constrained()->cascadeOnDelete();
            $table->string('type', 30);       // DocumentType enum
            $table->string('path');           // on the PRIVATE disk (storage/app/private)
            $table->string('mime_type', 100);
            $table->unsignedInteger('size_bytes');
            $table->timestampsTz();

            $table->index(['missing_report_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_documents');
    }
};
