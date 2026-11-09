<?php

use App\Enums\UserRole;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // string (not a Postgres ENUM type): adding a role later = no ALTER TYPE pain.
            // The PHP enum is what guarantees the allowed values.
            $table->string('role', 20)
                ->default(UserRole::Citizen->value)
                ->after('email')
                ->index(); // admin pages filter by role

            // E.164 format, e.g. +237690000000 (max 15 digits + "+")
            $table->string('phone', 16)->nullable()->unique()->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['phone']);
            $table->dropIndex(['role']);
            $table->dropColumn(['role', 'phone']);
        });
    }
};
