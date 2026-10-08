<?php

namespace App\Services;

use App\Models\AcademicSession;
use App\Models\Section;
use App\Models\Semester;
use App\Models\StudentProfile;
use Illuminate\Support\Facades\DB;

class SemesterPromotionService
{
    /**
     * Mid-year: odd semester -> next even semester, same session/batch.
     */
    public function promoteMidYear(iterable $studentIds, ?int $targetSectionId = null): array
    {
        return $this->promote($studentIds, 'mid_year', null, $targetSectionId);
    }

    /**
     * Year-end: even semester -> next odd semester, move to target session (same batch).
     */
    public function promoteYearEnd(iterable $studentIds, int $targetSessionId, ?int $targetSectionId = null): array
    {
        return $this->promote($studentIds, 'year_end', $targetSessionId, $targetSectionId);
    }

    protected function promote(iterable $studentIds, string $type, ?int $targetSessionId, ?int $targetSectionId): array
    {
        $summary = [
            'promoted' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        $targetSession = null;
        if ($type === 'year_end') {
            $targetSession = AcademicSession::find($targetSessionId);
            if (! $targetSession) {
                $summary['errors'][] = 'Target session not found.';

                return $summary;
            }
        }

        $profiles = StudentProfile::with(['semester', 'section', 'user'])
            ->whereIn('user_id', collect($studentIds)->all())
            ->get()
            ->keyBy('user_id');

        DB::transaction(function () use ($profiles, $type, $targetSession, $targetSectionId, &$summary) {
            foreach ($profiles as $profile) {
                $result = $this->promoteOne($profile, $type, $targetSession, $targetSectionId);
                if ($result === true) {
                    $summary['promoted']++;
                } elseif (is_string($result)) {
                    $summary['skipped']++;
                    $summary['errors'][] = ($profile->user->name ?? 'Student #'.$profile->user_id).': '.$result;
                }
            }
        });

        return $summary;
    }

    protected function promoteOne(
        StudentProfile $profile,
        string $type,
        ?AcademicSession $targetSession,
        ?int $targetSectionId
    ): bool|string {
        $semester = $profile->semester;
        if (! $semester) {
            return 'No semester assigned.';
        }

        $number = (int) $semester->semester_number;
        $isOdd = $number % 2 === 1;

        if ($type === 'mid_year' && ! $isOdd) {
            return 'Mid-year promote requires an odd semester (1, 3, 5…).';
        }

        if ($type === 'year_end' && $isOdd) {
            return 'Year-end promote requires an even semester (2, 4, 6…).';
        }

        $nextSemester = Semester::where('course_id', $profile->course_id)
            ->where('semester_number', $number + 1)
            ->first();

        if (! $nextSemester) {
            return 'Already at final semester (course complete).';
        }

        $newSessionId = $profile->session_id;
        $newBatchId = $profile->batch_id;

        if ($type === 'year_end') {
            if (! $targetSession) {
                return 'Target session required.';
            }
            if ((int) $targetSession->batch_id !== (int) $profile->batch_id) {
                return 'Target session must belong to the same batch.';
            }
            $newSessionId = $targetSession->id;
            $newBatchId = $targetSession->batch_id;
        }

        $newSectionId = $targetSectionId;
        if (! $newSectionId) {
            $newSectionId = $this->resolveMatchingSection($profile->section, $nextSemester->id)?->id;
        }

        if ($newSectionId) {
            $sectionOk = Section::where('id', $newSectionId)
                ->where('semester_id', $nextSemester->id)
                ->exists();
            if (! $sectionOk) {
                return 'Target section does not belong to the next semester.';
            }
        } else {
            return 'Could not resolve target section; please select one.';
        }

        $old = [
            'batch_id' => $profile->batch_id,
            'session_id' => $profile->session_id,
            'semester_id' => $profile->semester_id,
            'section_id' => $profile->section_id,
        ];

        $profile->update([
            'batch_id' => $newBatchId,
            'session_id' => $newSessionId,
            'semester_id' => $nextSemester->id,
            'section_id' => $newSectionId,
        ]);

        AuditLogger::log(
            'promoted',
            $profile,
            $old,
            [
                'batch_id' => $newBatchId,
                'session_id' => $newSessionId,
                'semester_id' => $nextSemester->id,
                'section_id' => $newSectionId,
            ],
            sprintf(
                'Promoted %s (%s) %s Sem %d → Sem %d',
                $profile->user->name ?? 'student',
                $type,
                $type === 'mid_year' ? 'mid-year' : 'year-end',
                $number,
                $number + 1
            ),
            ['promotion_type' => $type]
        );

        return true;
    }

    protected function resolveMatchingSection(?Section $current, int $nextSemesterId): ?Section
    {
        if (! $current) {
            return Section::where('semester_id', $nextSemesterId)->orderBy('name')->first();
        }

        $match = Section::where('semester_id', $nextSemesterId)
            ->where(function ($q) use ($current) {
                $q->whereRaw('LOWER(code) = ?', [strtolower($current->code)])
                    ->orWhereRaw('LOWER(name) = ?', [strtolower($current->name)]);
            })
            ->first();

        return $match ?: Section::where('semester_id', $nextSemesterId)->orderBy('name')->first();
    }
}
