<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class VerificationTokenMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        /** @var User */
        $user = auth()->user();

        if (!$user) {
            abort(Response::HTTP_UNAUTHORIZED);
        }

        $verificationToken = $request->header('Verification-Token', $request->query('_verification_token'));

        if (!$verificationToken) {
            throw new HttpException(Response::HTTP_FORBIDDEN, __('error_messages.verification_token.required'));
        }

        if (!$user->hasValidVerificationToken($verificationToken)) {
            throw new HttpException(Response::HTTP_FORBIDDEN, __('error_messages.verification_token.invalid'));
        }

        return $next($request);
    }
}
