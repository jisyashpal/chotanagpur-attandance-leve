<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('academic_sessions', function (Blueprint $table) {
            $table->foreignId('batch_id')->nullable()->after('id')->constrained('batches')->nullOnDelete();
        });

        $this->backfillBatchIds();
    }

    private function backfillBatchIds(): void
    {
        if (! Schema::hasTable('courses') || ! Schema::hasTable('batches')) {
            return;
        }

        $courses = DB::table('courses')->orderBy('id')->get();
        if ($courses->isEmpty()) {
            return;
        }

        $llb = $courses->first(fn ($c) => str_contains(strtoupper((string) $c->code), 'LLB'))
            ?? $courses->first();
        $llm = $courses->first(fn ($c) => str_contains(strtoupper((string) $c->code), 'LLM'));

        $sessions = DB::table('academic_sessions')->orderBy('id')->get();

        $parseEndYear = function (string $name, int $startYear): ?int {
            if (! preg_match('/^(\d{4})-(\d{2}|\d{4})$/', $name, $m)) {
                return null;
            }

            return strlen($m[2]) === 4
                ? (int) $m[2]
                : (int) (substr((string) $startYear, 0, 2).$m[2]);
        };

        $isCohortName = function (string $name, int $startYear) use ($parseEndYear): bool {
            $endYear = $parseEndYear($name, $startYear);
            if ($endYear === null) {
                return false;
            }

            return ($endYear - $startYear) > 1;
        };

        foreach ($sessions as $session) {
            $name = (string) $session->name;
            $startYear = (int) ($session->session_year ?: substr($name, 0, 4) ?: date('Y'));

            if ($isCohortName($name, $startYear)) {
                $endYear = $parseEndYear($name, $startYear);
                $courseId = ($endYear - $startYear) >= 3
                    ? $llb->id
                    : ($llm?->id ?? $llb->id);

                $batchId = $this->findOrCreateBatch(
                    $courseId,
                    $name,
                    $startYear,
                    $endYear,
                    (bool) $session->is_active
                );

                DB::table('academic_sessions')->where('id', $session->id)->update([
                    'batch_id' => $batchId,
                ]);

                continue;
            }

            $courseId = $llb->id;
            $existing = DB::table('batches')
                ->where('course_id', $courseId)
                ->where('start_year', $startYear)
                ->orderBy('id')
                ->first();

            if ($existing) {
                $batchId = $existing->id;
            } else {
                $endYear = $startYear + 3;
                $batchName = $startYear.'-'.substr((string) $endYear, -2);
                $batchId = $this->findOrCreateBatch($courseId, $batchName, $startYear, $endYear, true);
            }

            DB::table('academic_sessions')->where('id', $session->id)->update([
                'batch_id' => $batchId,
            ]);
        }
    }

    private function findOrCreateBatch(int $courseId, string $name, int $startYear, int $endYear, bool $isActive): int
    {
        $existing = DB::table('batches')
            ->where('course_id', $courseId)
            ->where('name', $name)
            ->first();

        if ($existing) {
            return $existing->id;
        }

        return DB::table('batches')->insertGetId([
            'course_id' => $courseId,
            'name' => $name,
            'start_year' => $startYear,
            'end_year' => $endYear,
            'is_active' => $isActive,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::table('academic_sessions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('batch_id');
        });
    }
};
