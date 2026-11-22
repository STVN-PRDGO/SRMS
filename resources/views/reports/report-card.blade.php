<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Report card</title>
    <style>
        body { font-family: sans-serif; color: #111; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
    </style>
</head>
<body>
    <h1>Report card</h1>
    <p>{{ $student->user->name }} · {{ $student->student_no }}</p>
    <p>{{ $student->section->gradeLevel->name }} · {{ $student->section->name }} · {{ $period->name }}</p>
    <table>
        <thead>
            <tr><th>Subject</th><th>Grade</th><th>Letter</th><th>Remark</th></tr>
        </thead>
        <tbody>
            @foreach ($grades as $grade)
                <tr>
                    <td>{{ $grade->classSubject->subject->name }}</td>
                    <td>{{ $grade->raw_grade }}</td>
                    <td>{{ $grade->letter }}</td>
                    <td>{{ $grade->remark }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @if ($summary)
        <p>General average: {{ $summary->general_average }}</p>
        <p>Attendance: {{ $summary->attendance_rate }}%</p>
        <p>Level: {{ $summary->performanceLevel?->name }}</p>
    @endif
</body>
</html>
