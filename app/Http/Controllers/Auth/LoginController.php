<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class LoginController extends Controller
{
    /**
     * Check the credentials and issue a Sanctum personal access token.
     * No session is used: the client must send the returned token back
     * as an "Authorization: Bearer <token>" header.
     */
    public function store(LoginRequest $request): JsonResponse
    {
        // Drop any user a previous request in this process cached on a guard.
        Auth::forgetGuards();

        // 1) The request itself already carries a currently-valid access token.
        //    Resolve it from the Authorization header (and a few real-client
        //    variants) against the personal_access_tokens table. Sessions and
        //    in-process guard caches are ignored on purpose.
        if ($this->requestCarriesActiveToken($request)) {
            return $this->alreadyAuthenticated();
        }

        $user = User::where('email', $request->input('email'))->first();

        if (! $user || ! Hash::check($request->input('password'), $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        // 2) This account ALREADY holds an active token -> refuse. One
        //    account, one token: a new one is only issued when there is none.
        if ($this->userHasActiveToken($user)) {
            return $this->alreadyAuthenticated();
        }

        return response()->json([
            'token' => $user->createToken('api')->plainTextToken,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->value,
            ],
        ]);
    }

    /**
     * Determine if THIS request carries a bearer (or equivalent) token that
     * still exists and is not expired in the database.
     */
    private function requestCarriesActiveToken(Request $request): bool
    {
        $token = $this->resolvePersonalAccessToken($request);

        return $token !== null && $this->tokenIsActive($token);
    }

    /**
     * Whether this account currently holds an access token that is actually in
     * use. A token only counts as "active" once it has been used on at least
     * one protected route (last_used_at is set). Rows that were created but
     * never used (abandoned leftovers from old tests, forgotten logins, ...)
     * are NOT considered active: they must never be able to lock an account
     * out of login, and logout can not remove them because nobody has their
     * secrets anymore.
     */
    private function userHasActiveToken(User $user): bool
    {
        return PersonalAccessToken::query()
            ->where('tokenable_type', $user->getMorphClass())
            ->where('tokenable_id', $user->getKey())
            ->whereNotNull('last_used_at')
            ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->exists();
    }

    private function tokenIsActive(PersonalAccessToken $token): bool
    {
        return $token->expires_at === null || $token->expires_at->isFuture();
    }

    /**
     * Resolve a personal access token from the request.
     *
     * Real clients often send the token with extra whitespace, without the
     * "Bearer " prefix, or as a JSON body field. Logout used to look only at
     * $request->bearerToken() and then return 204 even when that lookup
     * failed, leaving the row in personal_access_tokens and blocking the
     * next login.
     */
    private function resolvePersonalAccessToken(Request $request): ?PersonalAccessToken
    {
        foreach ($this->tokenCandidatesFrom($request) as $plain) {
            $token = PersonalAccessToken::findToken($plain);

            if ($token !== null) {
                return $token;
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private function tokenCandidatesFrom(Request $request): array
    {
        $candidates = [];

        $authorization = $request->header('Authorization');

        if (is_string($authorization) && $authorization !== '') {
            $authorization = trim($authorization);
            $authorization = trim($authorization, "\"'");

            if (preg_match('/^(Bearer|Token)\s+/i', $authorization) === 1) {
                $candidates[] = trim((string) preg_replace('/^(Bearer|Token)\s+/i', '', $authorization));
            } else {
                $candidates[] = $authorization;
            }
        }

        $bearer = $request->bearerToken();

        if (is_string($bearer) && $bearer !== '') {
            $candidates[] = trim($bearer);
        }

        $input = $request->input('token');

        if (is_string($input) && $input !== '') {
            $candidates[] = trim($input);
        }

        $candidates = array_values(array_unique(array_filter(
            $candidates,
            fn (string $candidate): bool => $candidate !== '',
        )));

        return $candidates;
    }

    /**
     * The single "already logged in" refusal, so both checks above answer
     * with the same 401 and the same clear message.
     */
    private function alreadyAuthenticated(): JsonResponse
    {
        return response()->json([
            'message' => 'You are already authenticated. Please log out before signing in again.',
        ], JsonResponse::HTTP_UNAUTHORIZED);
    }

    /**
     * Revoke the token that was used, so it stops working immediately.
     *
     * The logout route is intentionally NOT protected by 'auth:sanctum':
     * logging out must never fail with 401 (e.g. on a retried request, a
     * double-invoked logout, or a stale/already-revoked token). Instead we
     * resolve the token from the request and delete every token on that
     * account if it is still valid; if there is nothing left to revoke we
     * still answer 204, because logging out is idempotent.
     */
    public function destroy(Request $request): Response
    {
        Auth::forgetGuards();

        $token = $this->resolvePersonalAccessToken($request);

        if ($token !== null && $token->getKey() !== null) {
            PersonalAccessToken::query()
                ->where('tokenable_type', $token->tokenable_type)
                ->where('tokenable_id', $token->tokenable_id)
                ->delete();
        }

        if ($request->hasSession()) {
            if (Auth::guard('web')->check()) {
                Auth::guard('web')->logout();
            }

            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        Auth::forgetGuards();

        return response()->noContent();
    }
}
