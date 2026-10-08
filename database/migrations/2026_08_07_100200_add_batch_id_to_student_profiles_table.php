<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->foreignId('batch_id')->nullable()->after('user_id')->constrained('batches')->nullOnDelete();
        });

        $sessionColumns = Schema::getColumnListing('academic_sessions');
        if (! in_array('batch_id', $sessionColumns, true)) {
            return;
        }

        $profiles = DB::table('student_profiles')->whereNotNull('session_id')->get(['id', 'session_id']);
        foreach ($profiles as $profile) {
            $batchId = DB::table('academic_sessions')->where('id', $profile->session_id)->value('batch_id');
            if ($batchId) {
                DB::table('student_profiles')->where('id', $profile->id)->update(['batch_id' => $batchId]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('batch_id');
        });
    }
};
