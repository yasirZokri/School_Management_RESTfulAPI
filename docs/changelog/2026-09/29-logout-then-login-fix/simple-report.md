# Logging In Again After Logging Out Was Failing

## What was broken

After a user logged out successfully, trying to log in again immediately
returned the message "You are already authenticated. Please log out before
signing in again." — even though the logout had already finished.

The problem was a kind of "memory leftover" in the program. The authentication
system remembers which user is signed in for the current processing run. When
the user logged out, the sign-in record was removed from the database correctly,
**but the program's memory still held onto the old user.** So when the user tried
to sign in again, the program looked at its leftover memory, wrongly concluded
the user was still signed in, and refused the new login.

The fix was done in two places on the login/logout screen:

1. **On signing in:** the program now clears its leftover memory first and only
   then checks whether someone is already signed in. This way it always checks
   the current situation, never an old one.
2. **On signing out:** the program also clears its leftover memory first, so the
   logout always removes the correct sign-in record for this request.

Nothing else was changed. The logout still truly removes the sign-in record from
the database, and signing in without logging out first is still correctly blocked.

## Proof (real test results)

The exact reported sequence was written as a test and it **failed before the fix**
(login returned error 401 instead of success 200):

```
Expected response status code [200] but received 401.
```

After the fix the same test passes, together with the other checks:

```
Login -> logout -> log in again ............ PASSED (new login works, new token)
Logging in twice without logout ............ PASSED (still blocked, error 401)
Old token after logout on protected page ... PASSED (still rejected, error 401)
```

## Full test suite after the fix

```
+----------+----------+
| Tests    | 101/101  |
| Assertions | 292    |
+----------+----------+
```

All 101 tests pass. Code style check is also clean.