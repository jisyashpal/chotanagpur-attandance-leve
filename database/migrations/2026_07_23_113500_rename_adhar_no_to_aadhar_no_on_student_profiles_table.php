<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn('student_profiles', 'adhar_no') && ! Schema::hasColumn('student_profiles', 'aadhar_no')) {
            Schema::table('student_profiles', function (Blueprint $table) {
                $table->renameColumn('adhar_no', 'aadhar_no');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('student_profiles', 'aadhar_no') && ! Schema::hasColumn('student_profiles', 'adhar_no')) {
            Schema::table('student_profiles', function (Blueprint $table) {
                $table->renameColumn('aadhar_no', 'adhar_no');
            });
        }
    }
};
