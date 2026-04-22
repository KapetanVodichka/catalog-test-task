<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminTokenIsValid
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->cookie('admin_token');

        if (! is_string($token) || $token === '') {
            return $this->redirectToLogin();
        }

        $accessToken = PersonalAccessToken::findToken($token);

        if (! $accessToken || ! $accessToken->tokenable instanceof User) {
            return $this->redirectToLogin(true);
        }

        if ($accessToken->expires_at && $accessToken->expires_at->isPast()) {
            $accessToken->delete();

            return $this->redirectToLogin(true);
        }

        $request->setUserResolver(static fn (): User => $accessToken->tokenable);

        $accessToken->forceFill(['last_used_at' => now()])->save();

        return $next($request);
    }

    private function redirectToLogin(bool $forgetCookie = false): RedirectResponse
    {
        $response = redirect()->route('auth.login');

        if (! $forgetCookie) {
            return $response;
        }

        return $response->withCookie(Cookie::forget('admin_token'));
    }
}
