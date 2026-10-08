<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('faculty_subject_allocations', function (Blueprint $table) {
            $table->unsignedBigInteger('session_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('faculty_subject_allocations', function (Blueprint $table) {
            $table->unsignedBigInteger('session_id')->nullable(false)->change();
        });
    }
};
