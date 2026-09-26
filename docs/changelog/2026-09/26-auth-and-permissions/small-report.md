# Small report - what changed in this round

Date: 2026-09-26

## Deleted (and why)

| Deleted | Why |
| --- | --- |
| Public route `POST /api/register` | Anyone (including students) could create accounts. Registration doesn't fit a school system: admin must control account creation. |
| `app/Http/Controllers/Auth/RegisteredUserController.php` | The only controller behind that route - no longer needed. |
| `tests/Feature/Auth/RegistrationTest.php` | Tested the removed endpoint. |
| `user_id` input from student/teacher create/update requests | A client should not pick which login account a student/teacher gets. The admin flow builds the account itself. |

## Added (and why)

| Added | Why |
| --- | --- |
| `app/Policies/UserPolicy.php` | No policy existed for user accounts. Admin can view/create/update/delete; a user can view their own profile. |
| Self-view in `TeacherPolicy` and `StudentPolicy` | A teacher could not see their own profile before; a student could not see theirs. |
| Linked-account creation inside `StudentController@store` / `TeacherController@store` | Creating a student/teacher as admin now creates the User with the `student`/`teacher` role internally - the only way accounts are made. |
| `StoreStudentRequest` / `StoreTeacherRequest` fields `name`, `email`, `password` | The payload the admin sends so the linked account can be created (email unique, password min 8). |
| `SendPasswordResetLinkRequest` + `ResetPasswordRequest` Form Requests | Moved the last inline `$request->validate(...)` calls out of controllers - every write action now validates via a Form Request. |
| New permission tests (StudentTest / TeacherTest) | Every role x action is now verified: view list, view detail, view self, create, delete - 200 for allowed, 403 for denied. |

## Fixed (external drift)

- `routes/api.php` had lost its `/api/v1` prefix again (changed outside this session), so every resource returned 404. Restored `prefix('v1')`.

## Result

- 94 tests / 94 passing (251 assertions), Pint clean.
- No public account creation exists. Admin creates students/teachers (and their login accounts) only.