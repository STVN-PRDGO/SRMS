<?php

namespace App\Actions\Students;

use App\Actions\Users\CreateAccount;
use App\Enums\StudentStatus;
use App\Models\Section;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ImportStudents
{
    public function __construct(private readonly CreateAccount $createAccount) {}

    /**
     * CSV columns: name, email, student_no, section, birthdate, guardian_name, guardian_contact.
     * The section column is the section name within the chosen school year.
     *
     * @return array{created: int, skipped: int}
     */
    public function __invoke(string $csv, int $schoolYearId): array
    {
        $rows = $this->rows($csv);
        $created = 0;
        $skipped = 0;

        DB::transaction(function () use ($rows, $schoolYearId, &$created, &$skipped): void {
            foreach ($rows as $row) {
                if (Student::query()->where('student_no', $row['student_no'])->exists()) {
                    $skipped++;

                    continue;
                }

                $section = Section::query()
                    ->where('school_year_id', $schoolYearId)
                    ->where('name', $row['section'])
                    ->first();

                if ($section === null) {
                    throw ValidationException::withMessages([
                        'file' => 'A row refers to a section that is not in the selected school year.',
                    ]);
                }

                $user = ($this->createAccount)([
                    'name' => $row['name'],
                    'email' => $row['email'],
                ], 'student');

                Student::query()->create([
                    'user_id' => $user->id,
                    'student_no' => $row['student_no'],
                    'section_id' => $section->id,
                    'birthdate' => $row['birthdate'] ?: null,
                    'guardian_name' => $row['guardian_name'] ?: null,
                    'guardian_contact' => $row['guardian_contact'] ?: null,
                    'status' => StudentStatus::Active,
                ]);

                $created++;
            }
        });

        return ['created' => $created, 'skipped' => $skipped];
    }

    /**
     * @return list<array{name: string, email: string, student_no: string, section: string, birthdate: string, guardian_name: string, guardian_contact: string}>
     */
    private function rows(string $csv): array
    {
        $lines = preg_split('/\r\n|\n|\r/', trim($csv)) ?: [];
        $header = str_getcsv(array_shift($lines) ?: '');
        $expected = ['name', 'email', 'student_no', 'section', 'birthdate', 'guardian_name', 'guardian_contact'];

        if ($header !== $expected) {
            throw ValidationException::withMessages([
                'file' => 'The CSV header must be: '.implode(', ', $expected).'.',
            ]);
        }

        $rows = [];

        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }

            $values = str_getcsv($line);
            $row = array_combine($expected, array_pad($values, count($expected), ''));

            if ($row === false || $row['name'] === '' || $row['email'] === '' || $row['student_no'] === '' || $row['section'] === '') {
                throw ValidationException::withMessages([
                    'file' => 'Each student row needs a name, email, student number, and section.',
                ]);
            }

            $rows[] = $row;
        }

        return $rows;
    }
}
