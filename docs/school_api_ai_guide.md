# School Management API - Improved Design & AI Start Guide

Companion to the original **School Management API - Training ERD**. It lists the improvements to make before writing migrations, and tells the AI tool (Cursor) exactly how to start. Stack: Laravel API-first backend, Breeze + Sanctum already installed and working.


## 1. START HERE - Instructions for the AI tool

Read this whole section before writing any code. The project already has a base structure, so the goal is to **understand what exists, review the design, agree on a plan, and only then build in small phases**.


### Current project state (do not redo)

- Laravel is installed with API support (routes/api.php exists).
- Laravel Breeze is installed and Sanctum authentication works.
- Do NOT reinstall Laravel, Breeze or Sanctum, and do not overwrite the existing auth setup.


### Step 0 - Inspect the existing project (read-only)

1. Open and read: composer.json, bootstrap/app.php, routes/api.php, routes/web.php, config/auth.php, config/sanctum.php.
2. Read app/Models/User.php, the existing migrations (especially the users table), existing controllers, and the tests folder.
3. Identify which Breeze stack was installed and how login/logout/register currently respond (JSON or redirects).
4. Check the database driver in .env.example and the PHP/Laravel versions.
5. Write a short summary of what you found and any problems (for example, web-style redirects that should be JSON).


### Step 1 - Review the design (no code yet)

1. Read the original ERD PDF and Section 3 of this document (Recommended changes).
2. Challenge assumptions: list normalization, relationship, and authorization problems, including any I missed.
3. Ask me up to 5 clarifying questions (see Section 6) before proposing the plan.


### Step 2 - Propose a plan and wait for approval

1. Propose the final list of tables with key columns, foreign keys, unique constraints, and indexes.
2. Propose the phase order (Section 5) and what each phase will contain.
3. STOP and wait for my approval before creating any migration or model.


### Step 3 - Build one phase at a time

- Only build the phase I approve. Never generate the whole project at once.
- Each phase: migrations, models with relationships, factories, seeders, Form Requests, API Resources, Policies, and Feature tests.
- Run migrations and tests after each phase, fix failures, then STOP and summarize what was built and why.
- Explain every architectural decision briefly. Use Service classes only where they add real value.
- Keep the API stateless, JSON-only, versioned under /api/v1, and independent from any frontend.


## 2. Ground Rules

- Authorization is enforced in the API with Policies and Gates. Never rely on frontend filtering.
- Every query for school-scoped data must be limited to the user's school (global scope or policy check).
- Use Form Requests for validation and API Resources for output. No raw model dumps.
- Preserve history: never delete or overwrite enrollment, fee, payment, or salary records.
- Use database transactions for anything that touches money.
- Use pagination on all list endpoints and consistent error responses (JSON).
- Prefer a new migration over editing an already-run migration, unless the database is empty and I confirm.


## 3. Recommended Changes to the Original ERD


### 3.1 Relationship problems

- **Cross-school consistency:** nothing stops a student of school A from being enrolled in a class of school B, or a lesson from using a subject of another school. Validate in Form Requests/Policies (and consider composite checks).
- **fees.school_id is redundant:** the school can be derived from student -> school. Remove it, or keep it only as a deliberate historical snapshot.
- **Teacher-to-class link is missing:** the ERD says to verify a teacher may manage a class, but no table links them. Add **teaching_assignments** (teacher_id, class_id, subject_id, academic_year_id).
- **lessons has no school scope:** ensure teacher and subject belong to the same school.


### 3.2 Academic year

- academic_year is a free-text column repeated in classes and enrollments. This leads to typos and inconsistent data.
- Create an **academic_years** table now (id, school_id, name, starts_on, ends_on, is_current) and reference it by foreign key from classes, enrollments, and teaching_assignments. Changing this later is painful.


### 3.3 Money

- Use decimal(10,2) for every amount. Never float.
- fees.status (pending/partial/paid) can be computed from the sum of payments. Either compute it, or update it inside a transaction on every payment.
- payments.reference should be unique (nullable).
- Add unique(teacher_id, period) on salaries to prevent paying the same period twice.
- Prevent payments that exceed the remaining fee balance.


### 3.4 Users and roles

- users.role as a string is fine for training. Use a PHP enum (admin, teacher, student, staff) with casting; consider spatie/laravel-permission if roles grow.
- Add an admin role early: someone must create schools, classes, and subjects.
- student_number and employee_number should be unique per school: unique(school_id, student_number), unique(school_id, employee_number).
- students.user_id and teachers.user_id must be unique to enforce the 1:1 relationship.


### 3.5 Constraints, indexes, naming

- enrollments: unique(student_id, class_id, academic_year_id).
- subjects: unique(school_id, code).
- class_lesson: composite primary key (lesson_id, class_id), plus an index on class_id.
- Add softDeletes to students, teachers, fees, and payments.
- The table 'classes' is fine, but the model cannot be named Class (reserved word in PHP). Name it SchoolClass and set protected $table = 'classes' (or name the table school_classes).
- Add indexes on every foreign key and on frequent filters (status, due_date, academic_year_id).


## 4. Revised Table Summary

| Table | Key columns / changes |
|---|---|
| users | id, name, email (unique), password, role (enum), timestamps |
| schools | id, name, address, phone, status, timestamps |
| academic_years (NEW) | id, school_id, name, starts_on, ends_on, is_current |
| students | id, user_id (unique), school_id, student_number; unique(school_id, student_number); softDeletes |
| teachers | id, user_id (unique), school_id, employee_number; unique(school_id, employee_number); softDeletes |
| classes | id, school_id, academic_year_id, name, grade_level, section (model: SchoolClass) |
| subjects | id, school_id, name, code; unique(school_id, code) |
| teaching_assignments (NEW) | id, teacher_id, class_id, subject_id, academic_year_id; unique combination |
| lessons | id, teacher_id, subject_id, title, description, content, timestamps |
| class_lesson | lesson_id, class_id; PK (lesson_id, class_id) |
| enrollments | id, student_id, class_id, academic_year_id, status; unique(student_id, class_id, academic_year_id) |
| fees | id, student_id, fee_type, amount decimal, due_date, status; school_id removed or snapshot only |
| payments | id, fee_id, amount decimal, paid_at, payment_method, reference (unique, nullable) |
| salaries | id, teacher_id, amount decimal, period, paid_at, status; unique(teacher_id, period) |


## 5. Build Phases

One phase at a time. Stop for review after each phase.

| Phase | Scope |
|---|---|
| 0 | Inspect project, review ERD, agree on final schema (no code). |
| 1 | Users role enum, schools, students, teachers (migrations, models, factories, seeders, tests). |
| 2 | academic_years, classes, subjects, enrollments, teaching_assignments. |
| 3 | Lessons + class_lesson with Policies (teacher must be assigned to the class). |
| 4 | Fees, payments, salaries (transactions, decimal handling, balance rules). |
| 5 | Attendance, assignments, submissions, grades. |
| 6 | Hardening: API Resources everywhere, pagination, rate limiting, queues, caching, full test pass. |


## 6. Questions the AI Should Ask Me Before Planning

1. Can one student belong to more than one school, or only one at a time?
2. Can a teacher work at more than one school?
3. Do I want a separate admin/staff role now, or add school_staff later?
4. Should fee status be computed on the fly, or stored and updated on each payment?
5. Should the users table be changed with a new migration, or edited directly (fresh database)?


## 7. API Direction

- Versioned, stateless endpoints: /api/v1/auth/login, /api/v1/classes, /api/v1/lessons, /api/v1/students/me.
- Authentication via Sanctum tokens; every response is JSON, including validation and authorization errors.
- Feature tests for each endpoint: success case, validation failure, and forbidden access from another school or role.
