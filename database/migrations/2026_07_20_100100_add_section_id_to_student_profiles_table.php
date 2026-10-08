<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->foreignId('section_id')->nullable()->after('semester_id')->constrained('sections')->nullOnDelete();
        });

        $semesterIds = DB::table('student_profiles')
            ->whereNotNull('semester_id')
            ->distinct()
            ->pluck('semester_id');

        foreach ($semesterIds as $semesterId) {
            $defaultSectionId = DB::table('sections')
                ->where('semester_id', $semesterId)
                ->where('code', 'DEFAULT')
                ->value('id');

            if (!$defaultSectionId) {
                $defaultSectionId = DB::table('sections')->insertGetId([
                    'semester_id' => $semesterId,
                    'name' => 'Default',
                    'code' => 'DEFAULT',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('student_profiles')
                ->where('semester_id', $semesterId)
                ->whereNull('section_id')
                ->update(['section_id' => $defaultSectionId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('section_id');
        });
    }
};
