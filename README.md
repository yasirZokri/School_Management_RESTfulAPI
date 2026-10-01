# School Management API

A Laravel REST API for managing a school: users, students, teachers, classes, subjects, lessons, and financial records (teacher salaries and student payments). Authentication uses Laravel Sanctum bearer tokens.

## Features

- Token-based authentication (Laravel Sanctum)
- Role-based access control: `admin`, `teacher`, `student`
- Students and teachers with soft deletes (salary and payment history is kept)
- Academic years, classes, subjects, enrollments, and teaching assignments
- Lessons, teacher salaries, and student payments
- Password reset endpoints (email sending is not configured yet)
- Rate limiting on API requests
- Form Requests for validation on every write action
- Automated tests (94 passing)

## Tech Stack

- PHP / Laravel
- MySQL
- Laravel Sanctum (+ Breeze API scaffolding)
- PHPUnit

## Database (12 tables)

`users`, `schools`, `students`, `teachers`, `academic_years`, `classes`, `subjects`, `enrollments`, `teaching_assignments`, `lessons`, `teacher_salaries`, `student_payments`

## Permissions

| Action | Admin | Teacher | Student |
|---|---|---|---|
| View all users, teachers, students | Yes | No | No |
| View students list | Yes | Yes | No |
| View own profile | Yes | Yes | Yes |
| Create / update / delete students and teachers | Yes | No | No |

There is no public registration. The admin creates students and teachers together with their linked user accounts.

## Installation

```bash
git clone <your-repo-url>
cd <project-folder>
composer install
cp .env.example .env
php artisan key:generate
```

Create a MySQL database, then set your database values in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=school_management_api
DB_USERNAME=root
DB_PASSWORD=
```

Run migrations and start the server:

```bash
php artisan migrate
php artisan serve
```

## Authentication

Log in with email and password to receive a bearer token, then send it with every request:

```
Authorization: Bearer <your-token>
```

All routes are prefixed with `/api/v1`.

## Password Reset

Password reset endpoints exist, but no email service is connected yet, so reset emails are not delivered. To send real emails later, configure the `MAIL_*` values in `.env` (for example Mailtrap for development).

## Running Tests

```bash
php artisan test
```

## Notes

- Keep secrets only in `.env`. Never commit it.

## License

Add your license here.