# Non-Admin Users Got "Invalid Data" Instead of "Forbidden" — What Was Wrong and What I Changed

## What was actually broken

The system has many actions that only an administrator may do: creating a
student, creating a teacher, editing schools, classes, subjects, enrollments,
salaries, payments, and so on. When a non-admin tries such an action, the
system must answer with **"Forbidden"** (error 403) — that is its way of saying
"you are not allowed to do this, no matter what data you send."

That answer was mostly correct, but it had a flaw in the **order of the two
checks**:

- First, the system checked the data the person sent ("is the name there? is
  the password correct? is this field filled?"), and
- only afterwards it checked permission ("is this person an admin?").

Because of this wrong order, a non-admin who sent incomplete or wrong data got
the "Invalid Data" answer (error 422) instead of the "Forbidden" answer
(error 403). The hidden mistake: **permission was being checked too late, after
the data check had already run**.

## What I changed

I moved the permission check to run **first**, before any data check, on every
admin-only action (creating and editing students, teachers, schools, classes,
subjects, enrollments, academic years, salaries, payments, and teaching
assignments):

- A **non-admin** now always receives **"Forbidden" (403)** — even if the
  request data is completely empty or wrong.
- An **admin** is completely unaffected: with correct data the action succeeds
  (201/200), and with wrong data they still receive "Invalid Data" (422) so
  typos are still easy to fix.

I also added the missing **"confirm password"** field to the test requests, so
the test examples look like real, complete requests.

## Proof (real results shown below were actually run)

```
Non-admin sends a completely empty request to "create student"
    Answer: Forbidden (403)  — permission checked before data  ✔

Non-admin sends correct data to "create student"
    Answer: Forbidden (403)  — still blocked, as it should be   ✔

Admin sends a completely empty request to "create student"
    Answer: Invalid Data (422)  — typos still caught for admins ✔

The same behavior now applies to creating/editing teachers, schools,
classes, subjects, enrollments, academic years, salaries, payments,
and teaching assignments.
```

## Test suite result

```
Command:  php artisan test
Result:   118 tests passed / 118 total, 375 assertions
```
Code style check: clean.