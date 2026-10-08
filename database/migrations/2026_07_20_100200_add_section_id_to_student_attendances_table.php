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
        Schema::table('student_attendances', function (Blueprint $table) {
            $table->foreignId('section_id')->nullable()->after('semester_id')->constrained('sections')->nullOnDelete();
        });

        $studentSectionMap = DB::table('student_profiles')
            ->whereNotNull('section_id')
            ->pluck('section_id', 'user_id');

        DB::table('student_attendances')
            ->whereNull('section_id')
            ->orderBy('id')
            ->chunkById(500, function ($rows) use ($studentSectionMap) {
                foreach ($rows as $row) {
                    $mappedSectionId = $studentSectionMap->get($row->student_id);
                    if ($mappedSectionId) {
                        DB::table('student_attendances')
                            ->where('id', $row->id)
                            ->update(['section_id' => $mappedSectionId]);
                    }
                }
            });

        $remainingRows = DB::table('student_attendances')
            ->whereNull('section_id')
            ->whereNotNull('semester_id')
            ->select('id', 'semester_id')
            ->get();

        foreach ($remainingRows as $row) {
            $defaultSectionId = DB::table('sections')
                ->where('semester_id', $row->semester_id)
                ->where('code', 'DEFAULT')
                ->value('id');

            if (!$defaultSectionId) {
                $defaultSectionId = DB::table('sections')->insertGetId([
                    'semester_id' => $row->semester_id,
                    'name' => 'Default',
                    'code' => 'DEFAULT',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('student_attendances')
                ->where('id', $row->id)
                ->update(['section_id' => $defaultSectionId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_attendances', function (Blueprint $table) {
            $table->dropConstrainedForeignId('section_id');
        });
    }
};
