<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Stripe\WebhookSignature;
use Stripe\Exception\SignatureVerificationException;
use Laravel\Cashier\Http\Middleware\VerifyWebhookSignature;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class VerifyConnectWebhookSignature extends VerifyWebhookSignature
{
     /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Illuminate\Http\Response
     *
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     */
    public function handle($request, Closure $next)
    {
        try {
            WebhookSignature::verifyHeader(
                $request->getContent(),
                $request->header('Stripe-Signature'),
                config('cashier.connect.webhook.secret'),
                config('cashier.connect.webhook.tolerance')
            );
        } catch (SignatureVerificationException $exception) {
            throw new AccessDeniedHttpException($exception->getMessage(), $exception);
        }

        return $next($request);
    }
}
