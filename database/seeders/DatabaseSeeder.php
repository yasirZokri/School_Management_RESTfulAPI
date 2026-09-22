<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\AcademicYear;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentPayment;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherSalary;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Keep seeding simple and safe: if the admin already exists, the
        // database was seeded before, so we stop instead of duplicating rows.
        if (User::where('email', 'admin@example.com')->exists()) {
            $this->command->info('Database already seeded. Skipping.');

            return;
        }

        // 1. The admin who manages everything.
        User::factory()->role(UserRole::Admin)->create([
            'name' => 'School Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        // 2. One sample school.
        $school = School::factory()->create([
            'name' => 'Example International School',
        ]);

        // 3. A few students and teachers for that school.
        //    Each one gets its own login account automatically (see factories).
        Student::factory()->count(5)->for($school)->create();
        Teacher::factory()->count(3)->for($school)->create();

        // 4. One academic year for the school.
        $year = AcademicYear::factory()->for($school)->create([
            'name' => '2026 / 2027',
            'starts_on' => '2026-09-01',
            'ends_on' => '2027-06-30',
            'is_current' => true,
        ]);

        // 5. Subjects, then a few classes for that year.
        $subjects = Subject::factory()->count(4)->for($school)->create();
        $classes = SchoolClass::factory()->count(3)->for($school)->create([
            'academic_year_id' => $year->id,
        ]);

        // 6. Enroll every student into the first class.
        $firstClass = $classes->first();
        foreach (Student::where('school_id', $school->id)->get() as $student) {
            Enrollment::create([
                'student_id' => $student->id,
                'class_id' => $firstClass->id,
                'academic_year_id' => $year->id,
                'status' => 'active',
            ]);
        }

        // 7. Give each teacher a subject to teach in one of the classes.
        foreach (Teacher::where('school_id', $school->id)->get() as $i => $teacher) {
            TeachingAssignment::create([
                'teacher_id' => $teacher->id,
                'class_id' => $classes[$i % $classes->count()]->id,
                'subject_id' => $subjects[$i]->id,
                'academic_year_id' => $year->id,
            ]);
        }

        // 8. Two sample lessons for every teaching assignment.
        foreach (TeachingAssignment::all() as $assignment) {
            Lesson::factory()->count(2)->create([
                'teaching_assignment_id' => $assignment->id,
            ]);
        }

        // 9. One salary per teacher for the current month.
        foreach (Teacher::where('school_id', $school->id)->get() as $teacher) {
            TeacherSalary::create([
                'teacher_id' => $teacher->id,
                'amount' => '3000.00',
                'month' => '2026-09',
                'paid_at' => now(),
            ]);
        }

        // 10. One payment per student.
        foreach (Student::where('school_id', $school->id)->get() as $student) {
            StudentPayment::create([
                'student_id' => $student->id,
                'amount' => '500.00',
                'purpose' => 'tuition',
                'paid_at' => now(),
                'payment_method' => 'cash',
            ]);
        }
    }
}
