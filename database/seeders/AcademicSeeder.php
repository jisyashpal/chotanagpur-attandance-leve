<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AcademicSession;
use App\Models\Batch;
use App\Models\Course;
use App\Models\Section;
use App\Models\Semester;
use App\Models\Subject;

class AcademicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Courses
        $llm1 = Course::firstOrCreate(
            ['code' => 'LLM1'],
            ['name' => 'LL.M. (1 Year Course)', 'code' => 'LLM1']
        );
        $bballb = Course::firstOrCreate(
            ['code' => 'BBALLB5'],
            ['name' => 'B.B.A. LL.B. (5 Years Integrated Course)', 'code' => 'BBALLB5']
        );
        $llb = Course::firstOrCreate(
            ['code' => 'LLB3'],
            ['name' => 'LL.B. (3 Year Course)', 'code' => 'LLB3']
        );
        $llm2 = Course::firstOrCreate(
            ['code' => 'LLM2'],
            ['name' => 'LL.M. (2 Year Course)', 'code' => 'LLM2']
        );

        // 2. Batches — each with current session 2026-27 only
        $this->seedBatchWithSessions($bballb, [
            ['name' => '2026-2031', 'start_year' => 2026, 'end_year' => 2031, 'is_active' => true],
        ], [
            '2026-2031' => [['2026-27', 2026, true]],
        ]);

        $this->seedBatchWithSessions($llm2, [
            ['name' => '2025-2027', 'start_year' => 2025, 'end_year' => 2027, 'is_active' => true],
        ], [
            '2025-2027' => [['2026-27', 2026, true]],
        ]);

        $this->seedBatchWithSessions($llm1, [
            ['name' => '2026-2027', 'start_year' => 2026, 'end_year' => 2027, 'is_active' => true],
        ], [
            '2026-2027' => [['2026-27', 2026, true]],
        ]);

        $this->seedBatchWithSessions($llb, [
            ['name' => '2024-2027', 'start_year' => 2024, 'end_year' => 2027, 'is_active' => true],
            ['name' => '2025-2028', 'start_year' => 2025, 'end_year' => 2028, 'is_active' => true],
            ['name' => '2026-2029', 'start_year' => 2026, 'end_year' => 2029, 'is_active' => true],
        ], [
            '2024-2027' => [['2026-27', 2026, true]],
            '2025-2028' => [['2026-27', 2026, true]],
            '2026-2029' => [['2026-27', 2026, true]],
        ]);

        // 3. Semesters (+ subjects where already defined)
        $this->seedLLM1Year($llm1);                 // 1 year → Semester I subjects (II later)
        $this->seedBBALLB($bballb);                  // 5 years → 10 semesters + subjects
        $this->seedLLB($llb);                        // 3 years → 6 semesters + subjects
        $this->seedLLM($llm2);                       // 2 years → 4 semesters + subjects
    }

    /**
     * @param  array<int, array{name: string, start_year: int, end_year: int, is_active: bool}>  $batches
     * @param  array<string, array<int, array{0: string, 1: int, 2: bool}>>  $sessionsByBatchName
     */
    private function seedBatchWithSessions(Course $course, array $batches, array $sessionsByBatchName): void
    {
        foreach ($batches as $batchData) {
            $batch = Batch::updateOrCreate(
                ['course_id' => $course->id, 'name' => $batchData['name']],
                [
                    'start_year' => $batchData['start_year'],
                    'end_year' => $batchData['end_year'],
                    'is_active' => $batchData['is_active'],
                ]
            );

            foreach ($sessionsByBatchName[$batchData['name']] ?? [] as [$name, $year, $active]) {
                AcademicSession::updateOrCreate(
                    ['batch_id' => $batch->id, 'name' => $name],
                    ['session_year' => $year, 'is_active' => $active]
                );
            }
        }
    }

    /**
     * @param  array<int, string>  $codes
     */
    private function ensureSections(Semester $semester, array $codes = ['A', 'B']): void
    {
        foreach ($codes as $code) {
            Section::firstOrCreate(
                ['semester_id' => $semester->id, 'code' => $code],
                ['name' => $code]
            );
        }
    }

    private function partForSemester(int $num): string
    {
        $yearIndex = (int) ceil($num / 2);
        $roman = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V'];

        return 'Part '.($roman[$yearIndex] ?? (string) $yearIndex);
    }

    private function seedSemestersOnly(Course $course, int $count): void
    {
        for ($num = 1; $num <= $count; $num++) {
            $semester = Semester::firstOrCreate(
                ['course_id' => $course->id, 'semester_number' => $num],
                [
                    'name' => 'Semester '.['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X'][$num],
                    'course_id' => $course->id,
                    'semester_number' => $num,
                    'part' => $this->partForSemester($num),
                ]
            );

            $this->ensureSections($semester);
        }
    }

    private function seedLLM1Year($course)
    {
        // Ensure both semesters exist; subjects for Semester II can be added later
        for ($num = 1; $num <= 2; $num++) {
            $semester = Semester::firstOrCreate(
                ['course_id' => $course->id, 'semester_number' => $num],
                [
                    'name' => 'Semester '.['', 'I', 'II'][$num],
                    'course_id' => $course->id,
                    'semester_number' => $num,
                    'part' => $this->partForSemester($num),
                ]
            );
            $this->ensureSections($semester);
        }

        $semester1 = Semester::where('course_id', $course->id)->where('semester_number', 1)->first();

        $subjects = [
            ['code' => 'LLM01', 'name' => 'Legal Philosophy'],
            ['code' => 'LLM02', 'name' => 'Research Methodology'],
            ['code' => 'LLM03', 'name' => 'Law and Justice in a Globalizing World'],
            ['code' => 'LLM04', 'name' => 'Comparative Public Law'],
            ['code' => 'LLM05', 'name' => 'Seminar Paper-I'],
            ['code' => 'LLM06', 'name' => 'Judicial Process'],
            ['code' => 'LLM07', 'name' => 'Law related to women, child and indigenous people'],
        ];

        foreach ($subjects as $subjectData) {
            Subject::updateOrCreate(
                ['code' => $subjectData['code']],
                ['name' => $subjectData['name'], 'semester_id' => $semester1->id]
            );
        }
    }

    private function seedBBALLB($course)
    {
        $semestersData = [
            1 => [
                'name' => 'Semester I',
                'subjects' => [
                    ['code' => 'BBAC101', 'name' => 'General English Including Business Communications'],
                    ['code' => 'LLBC101', 'name' => 'Constitutional Law I'],
                    ['code' => 'LLBC102', 'name' => 'Law of Contracts I (General Principles & Specific Reliefs)'],
                    ['code' => 'LLBH103', 'name' => 'Law of Torts (incl. MV Act & Consumer Protection Act)'],
                    ['code' => 'BBAC102', 'name' => 'Account & Finance including Financial Accounting'],
                    ['code' => 'BBAC103', 'name' => 'Management Theory and Practice'],
                ],
            ],
            2 => [
                'name' => 'Semester II',
                'subjects' => [
                    ['code' => 'LLBH201', 'name' => 'English Literature including Legal Writings'],
                    ['code' => 'LLBC202', 'name' => 'Constitutional Law II'],
                    ['code' => 'LLBC203', 'name' => 'Law of Contracts II'],
                    ['code' => 'BBAC201', 'name' => 'Managerial Skill Development'],
                    ['code' => 'BBAC202', 'name' => 'Organizational Behaviour'],
                    ['code' => 'BBAC203', 'name' => 'Corporate Governance'],
                ],
            ],
            3 => [
                'name' => 'Semester III',
                'subjects' => [
                    ['code' => 'LLBC301', 'name' => 'International Organizations'],
                    ['code' => 'LLBC302', 'name' => 'Family Law I'],
                    ['code' => 'LLBC303', 'name' => 'Law of Crimes (BNS, 2023)'],
                    ['code' => 'BBAC301', 'name' => 'Business Mathematics'],
                    ['code' => 'BBAC302', 'name' => 'Human Resource Management'],
                    ['code' => 'BBAC303', 'name' => 'Quantitative Analysis'],
                ],
            ],
            4 => [
                'name' => 'Semester IV',
                'subjects' => [
                    ['code' => 'LLBC401', 'name' => 'Law on Agricultural Finance'],
                    ['code' => 'LLBC402', 'name' => 'Bhartiya Nagrik Suraksha Sanhita 2023'],
                    ['code' => 'LLBC403', 'name' => 'Family Law II'],
                    ['code' => 'BBAC401', 'name' => 'Marketing Management'],
                    ['code' => 'BBAC402', 'name' => 'Technology & Innovation Management'],
                    ['code' => 'BBAC403', 'name' => 'Business Statistics'],
                ],
            ],
            5 => [
                'name' => 'Semester V',
                'subjects' => [
                    ['code' => 'LLBC501', 'name' => 'Administrative Law'],
                    ['code' => 'LLBC502', 'name' => 'Law of Property & Easement'],
                    ['code' => 'LLBC503', 'name' => 'Law of Civil Procedure & Limitation'],
                    ['code' => 'LLBC504', 'name' => 'Company Law'],
                    ['code' => 'BBAC501', 'name' => 'Business Environment'],
                    ['code' => 'BBAC502', 'name' => 'Entrepreneurship & Small Business Management'],
                ],
            ],
            6 => [
                'name' => 'Semester VI',
                'subjects' => [
                    ['code' => 'LLBC601', 'name' => 'Jurisprudence'],
                    ['code' => 'LLBH602', 'name' => 'Intellectual Property Law'],
                    ['code' => 'LLBC603', 'name' => 'Law of Evidence (Bharatiya Sakshya Adhiniyam, 2023)'],
                    ['code' => 'LLBCL604', 'name' => 'Clinical Paper I: Moot Court Exercise, Advocacy & Internship'],
                    ['code' => 'BBAC601', 'name' => 'Business Policy & Strategic Management'],
                    ['code' => 'BBAC602', 'name' => 'Management Information System & E-Governance'],
                ],
            ],
            7 => [
                'name' => 'Semester VII',
                'subjects' => [
                    ['code' => 'LLBC701', 'name' => 'Labour & Industrial Law-I'],
                    ['code' => 'LLBC702', 'name' => 'Environmental Law'],
                    ['code' => 'LLBC703', 'name' => 'Principles of Taxation Law-I'],
                    ['code' => 'LLBEL704', 'name' => 'Elective I (Advanced Contracts / Financial Market Regulations / WTO Law)'],
                    ['code' => 'BBAC701', 'name' => 'Financial Management'],
                    ['code' => 'BBAC702', 'name' => 'Investment Banking & Finance'],
                ],
            ],
            8 => [
                'name' => 'Semester VIII',
                'subjects' => [
                    ['code' => 'LLBC801', 'name' => 'Labour & Industrial Law-II'],
                    ['code' => 'LLBH802', 'name' => 'Competition Law'],
                    ['code' => 'LLBC803', 'name' => 'Principles of Taxation Law-II'],
                    ['code' => 'LLBEL804', 'name' => 'Elective II (Service Law / Air & Space Law / Citizenship & Emigration Law)'],
                    ['code' => 'BBAC801', 'name' => 'Operational Research in Management'],
                    ['code' => 'BBAC802', 'name' => 'Production & Supply Chain Management'],
                ],
            ],
            9 => [
                'name' => 'Semester IX',
                'subjects' => [
                    ['code' => 'LLBC901', 'name' => 'Public International Law'],
                    ['code' => 'LLBH902', 'name' => 'Securities and Investment Laws'],
                    ['code' => 'LLBCL903', 'name' => 'Clinical Paper II: Drafting, Pleading & Conveyancing'],
                    ['code' => 'LLBCL904', 'name' => 'Clinical Paper III: Professional Ethics & Accounting System'],
                    ['code' => 'LLBE905', 'name' => 'Elective III (Corporate Finance / Financial & Systematic Fraud / IP Protection in India)'],
                    ['code' => 'LLBSE906', 'name' => 'Seminar Elective IV (Energy Law / Women & Children / Merger & Acquisitions / Animal Protection)'],
                ],
            ],
            10 => [
                'name' => 'Semester X',
                'subjects' => [
                    ['code' => 'LLBH1001', 'name' => 'International Trade & Business Law'],
                    ['code' => 'LLBH1002', 'name' => 'Offences Against Child & Juvenile Offences'],
                    ['code' => 'LLBH1003', 'name' => 'Interpretation of Statutes & Principles of Legislation'],
                    ['code' => 'LLBC1004', 'name' => 'Clinical Paper IV: Litigation Management & Alternative Dispute Resolution'],
                    ['code' => 'LLBE1005', 'name' => 'Elective V (International Banking & Finance / Infrastructural Development & Real Estate / Transportation Law)'],
                    ['code' => 'LLBSE1006', 'name' => 'Seminar Elective VI (Refugee Law / Disability Law / International Criminal Law / Media & Sports Law)'],
                ],
            ],
        ];

        foreach ($semestersData as $num => $data) {
            $semester = Semester::firstOrCreate(
                ['course_id' => $course->id, 'semester_number' => $num],
                [
                    'name' => $data['name'],
                    'course_id' => $course->id,
                    'semester_number' => $num,
                    'part' => $this->partForSemester($num),
                ]
            );

            $this->ensureSections($semester);

            foreach ($data['subjects'] as $subjectData) {
                Subject::updateOrCreate(
                    ['code' => $subjectData['code']],
                    ['name' => $subjectData['name'], 'semester_id' => $semester->id]
                );
            }
        }
    }

    private function seedLLB($course)
    {
        $semestersData = [
            1 => [
                'name' => 'Semester I',
                'subjects' => [
                    ['code' => 'LLB101', 'name' => 'Jurisprudence'],
                    ['code' => 'LLB102', 'name' => 'Contract-I'],
                    ['code' => 'LLB103', 'name' => 'Contract-II'],
                    ['code' => 'LLB104', 'name' => 'Family Law-I (Hindu Law)'],
                    ['code' => 'LLB105', 'name' => 'Family Law-II (Mohammaden Law)'],
                    ['code' => 'LLB106', 'name' => 'Bhartiya Nyaya Sanhita'],
                    ['code' => 'LLB107', 'name' => 'Constitutional Law'],
                    ['code' => 'LLB108', 'name' => 'Legal Writing (Including General English)'],
                    ['code' => 'LLB109', 'name' => 'Human Rights & International Law'],
                ],
            ],
            2 => [
                'name' => 'Semester II',
                'subjects' => [
                    ['code' => 'LLB201', 'name' => 'Jurisprudence'],
                    ['code' => 'LLB202', 'name' => 'Contract-I'],
                    ['code' => 'LLB203', 'name' => 'Contract-II'],
                    ['code' => 'LLB204', 'name' => 'Family Law-I (Hindu Law)'],
                    ['code' => 'LLB205', 'name' => 'Family Law-II (Mohammaden Law)'],
                    ['code' => 'LLB206', 'name' => 'Bhartiya Nyaya Sanhita'],
                    ['code' => 'LLB207', 'name' => 'Constitutional Law'],
                    ['code' => 'LLB208', 'name' => 'Legal Writing (Including General English)'],
                    ['code' => 'LLB209', 'name' => 'Human Rights & International Law'],
                ],
            ],
            3 => [
                'name' => 'Semester III',
                'subjects' => [
                    ['code' => 'LLB301', 'name' => 'Environmental Law'],
                    ['code' => 'LLB302A', 'name' => 'Land Laws (Elective/Optional)'],
                    ['code' => 'LLB302B', 'name' => 'Information Technology Law & Cyber Law (Elective/Optional)'],
                    ['code' => 'LLB303', 'name' => 'Bhartiya Nagrik Suraksha Sanhita & Juvenile Justice Act'],
                    ['code' => 'LLB304', 'name' => 'Property Law'],
                    ['code' => 'LLB305', 'name' => 'Administrative Law'],
                    ['code' => 'LLB306', 'name' => 'Company Law'],
                    ['code' => 'LLB307A', 'name' => 'Legal History (Elective/Optional)'],
                    ['code' => 'LLB308B', 'name' => 'Insolvency and Bankruptcy Laws (Elective/Optional)'],
                    ['code' => 'LLB309', 'name' => 'Practical Training'],
                ],
            ],
            4 => [
                'name' => 'Semester IV',
                'subjects' => [
                    ['code' => 'LLB401', 'name' => 'Environmental Law'],
                    ['code' => 'LLB402A', 'name' => 'Land Laws (Elective/Optional)'],
                    ['code' => 'LLB402B', 'name' => 'Information Technology Law & Cyber Law (Elective/Optional)'],
                    ['code' => 'LLB403', 'name' => 'Bhartiya Nagrik Suraksha Sanhita & Probation of Offenders Act'],
                    ['code' => 'LLB404', 'name' => 'Property Law'],
                    ['code' => 'LLB405', 'name' => 'Administrative Law'],
                    ['code' => 'LLB406', 'name' => 'Company Law'],
                    ['code' => 'LLB407A', 'name' => 'Legal History (Elective/Optional)'],
                    ['code' => 'LLB407B', 'name' => 'Insolvency and Bankruptcy Laws (Elective/Optional)'],
                    ['code' => 'LLB408', 'name' => 'Practical Training'],
                ],
            ],
            5 => [
                'name' => 'Semester V',
                'subjects' => [
                    ['code' => 'LLB501', 'name' => 'Bhartiya Sakshya Adhiniyam'],
                    ['code' => 'LLB502', 'name' => 'Torts & Consumer Protection Laws'],
                    ['code' => 'LLB503', 'name' => 'Civil Procedure Code & Limitation Act'],
                    ['code' => 'LLB504', 'name' => 'Arbitration, Conciliation & Alternative Dispute Resolution System'],
                    ['code' => 'LLB505', 'name' => 'Labour Law'],
                    ['code' => 'LLB506A', 'name' => 'Interpretation of Statute (Elective/Optional)'],
                    ['code' => 'LLB506B', 'name' => 'Insurance Law (Elective/Optional)'],
                    ['code' => 'LLB507', 'name' => 'Law of Taxation'],
                    ['code' => 'LLB507A', 'name' => 'Criminology & Penology (Elective/Optional)'],
                    ['code' => 'LLB507B', 'name' => 'Law and Medicine (Elective/Optional)'],
                    ['code' => 'LLB507C', 'name' => 'Intellectual Property Right (Elective/Optional)'],
                    ['code' => 'LLB508', 'name' => 'Practical Training'],
                ],
            ],
            6 => [
                'name' => 'Semester VI',
                'subjects' => [
                    ['code' => 'LLB601', 'name' => 'Bhartiya Sakshya Adhiniyam'],
                    ['code' => 'LLB602', 'name' => 'Torts & Consumer Protection Laws'],
                    ['code' => 'LLB603', 'name' => 'Civil Procedure Code & Limitation Act'],
                    ['code' => 'LLB604', 'name' => 'Arbitration, Conciliation & Alternative Dispute Resolution System'],
                    ['code' => 'LLB605', 'name' => 'Labour Law'],
                    ['code' => 'LLB606A', 'name' => 'Interpretation of Statute (Elective/Optional)'],
                    ['code' => 'LLB606B', 'name' => 'Insurance Law (Elective/Optional)'],
                    ['code' => 'LLB607', 'name' => 'Law of Taxation'],
                    ['code' => 'LLB607A', 'name' => 'Criminology & Penology (Elective/Optional)'],
                    ['code' => 'LLB607B', 'name' => 'Law and Medicine (Elective/Optional)'],
                    ['code' => 'LLB607C', 'name' => 'Intellectual Property Right (Elective/Optional)'],
                    ['code' => 'LLB608', 'name' => 'Practical Training'],
                ],
            ],
        ];

        foreach ($semestersData as $num => $data) {
            $part = match (true) {
                $num <= 2 => 'Part I',
                $num <= 4 => 'Part II',
                $num <= 6 => 'Part III',
                default => 'Part I'
            };

            $semester = Semester::firstOrCreate(
                ['course_id' => $course->id, 'semester_number' => $num],
                ['name' => $data['name'], 'course_id' => $course->id, 'semester_number' => $num, 'part' => $part]
            );

            $this->ensureSections($semester, ['A', 'B', 'C']);

            foreach ($data['subjects'] as $subjectData) {
                Subject::updateOrCreate(
                    ['code' => $subjectData['code']],
                    ['name' => $subjectData['name'], 'semester_id' => $semester->id]
                );
            }
        }
    }

    private function seedLLM($course)
    {
        $semestersData = [
            1 => [
                'name' => 'Semester I',
                'subjects' => [
                    ['code' => 'LLM101', 'name' => 'Legal Philosophy'],
                    ['code' => 'LLM102', 'name' => 'Comparative Constitutional Law'],
                    ['code' => 'LLM103', 'name' => 'Legal and Social Science Research Methodology'],
                    ['code' => 'LLM104', 'name' => 'Law & Justice in Globalizing World'],
                ],
            ],
            2 => [
                'name' => 'Semester II',
                'subjects' => [
                    // Group I: Constitutional Law
                    ['code' => 'LLM201', 'name' => 'Constitutional History of India and United Kingdom (Group I: Constitutional Law)'],
                    ['code' => 'LLM202', 'name' => 'Comparative Federalism (Group I: Constitutional Law)'],
                    ['code' => 'LLM203', 'name' => 'Judicial Process (Group I: Constitutional Law)'],
                    ['code' => 'LLM204', 'name' => 'Law related to women, child and indigenous people (Group I: Constitutional Law)'],
                    // Group II: Contract and Insurance
                    ['code' => 'LLM205', 'name' => 'General Principles of Contract (Group II: Contract and Insurance)'],
                    ['code' => 'LLM206', 'name' => 'Specific Contract (Group II: Contract and Insurance)'],
                    ['code' => 'LLM207', 'name' => 'Insurance (Group II: Contract and Insurance)'],
                    ['code' => 'LLM208', 'name' => 'Trade Law (Group II: Contract and Insurance)'],
                    // Group III: Environmental Law
                    ['code' => 'LLM209', 'name' => 'International Environmental Law in India (Group III: Environmental Law)'],
                    ['code' => 'LLM210', 'name' => 'Environmental Law in India (Group III: Environmental Law)'],
                    ['code' => 'LLM211', 'name' => 'Environmental and Current Social Problems (Group III: Environmental Law)'],
                    ['code' => 'LLM212', 'name' => 'Intellectual Property Rights (Group III: Environmental Law)'],
                ],
            ],
            3 => [
                'name' => 'Semester III',
                'subjects' => [
                    // Group I: Criminal Law
                    ['code' => 'LLM301', 'name' => 'Criminology and Penology (Group I: Criminal Law)'],
                    ['code' => 'LLM302', 'name' => 'Law of Crimes in India (Group I: Criminal Law)'],
                    ['code' => 'LLM303', 'name' => 'Crimes against social and economic Security and problems of their Control (Group I: Criminal Law)'],
                    ['code' => 'LLM304', 'name' => 'Cyber Crimes (Group I: Criminal Law)'],
                    // Group II: Business Organization
                    ['code' => 'LLM305', 'name' => 'Business Organization (Company Law Excluding Management) (Group II: Business Organization)'],
                    ['code' => 'LLM306', 'name' => 'Business Organization (Company Management and Administration) (Group II: Business Organization)'],
                    ['code' => 'LLM307', 'name' => 'Regulation of Labour Management Relations (Group II: Business Organization)'],
                    ['code' => 'LLM308', 'name' => 'Competition Law (Group II: Business Organization)'],
                    // Group III: Human Rights
                    ['code' => 'LLM309', 'name' => 'Human Rights Jurisprudence (Group III: Human Rights)'],
                    ['code' => 'LLM310', 'name' => 'Human Rights Law in India (Group III: Human Rights)'],
                    ['code' => 'LLM311', 'name' => 'International Law of Human Rights (Group III: Human Rights)'],
                    ['code' => 'LLM312', 'name' => 'Law of Refugees and Internally Displaced Persons (IDP) (Group III: Human Rights)'],
                ],
            ],
            4 => [
                'name' => 'Semester IV',
                'subjects' => [
                    ['code' => 'LLM401', 'name' => 'Interpretation of Statute'],
                    ['code' => 'LLM402', 'name' => 'Dissertation – I'],
                    ['code' => 'LLM403', 'name' => 'Dissertation – II'],
                    ['code' => 'LLM404', 'name' => 'Viva-Voce'],
                ],
            ],
        ];

        foreach ($semestersData as $num => $data) {
            $part = match (true) {
                $num <= 2 => 'Part I',
                $num <= 4 => 'Part II',
                default => 'Part I'
            };

            $semester = Semester::firstOrCreate(
                ['course_id' => $course->id, 'semester_number' => $num],
                ['name' => $data['name'], 'course_id' => $course->id, 'semester_number' => $num, 'part' => $part]
            );

            $this->ensureSections($semester);

            foreach ($data['subjects'] as $subjectData) {
                Subject::updateOrCreate(
                    ['code' => $subjectData['code']],
                    ['name' => $subjectData['name'], 'semester_id' => $semester->id]
                );
            }
        }
    }
}
