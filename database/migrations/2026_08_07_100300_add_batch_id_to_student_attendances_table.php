<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_attendances', function (Blueprint $table) {
            $table->foreignId('batch_id')->nullable()->after('subject_id')->constrained('batches')->nullOnDelete();
        });

        $sessionColumns = Schema::getColumnListing('academic_sessions');
        if (! in_array('batch_id', $sessionColumns, true)) {
            return;
        }

        $rows = DB::table('student_attendances')->whereNotNull('session_id')->get(['id', 'session_id']);
        foreach ($rows as $row) {
            $batchId = DB::table('academic_sessions')->where('id', $row->session_id)->value('batch_id');
            if ($batchId) {
                DB::table('student_attendances')->where('id', $row->id)->update(['batch_id' => $batchId]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('student_attendances', function (Blueprint $table) {
            $table->dropConstrainedForeignId('batch_id');
        });
    }
};
