<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $nonce = base64_encode(random_bytes(16));
        $request->attributes->set('csp_nonce', $nonce);
        Vite::useCspNonce($nonce);

        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=(), usb=()');

        $scriptSrc = "'self' 'nonce-{$nonce}' https://cdn.jsdelivr.net";

        // Alpine.js evalúa sus expresiones (x-show, @click, @click.away, etc.)
        // usando new Function()/eval, por lo que necesita 'unsafe-eval' también
        // en producción. Sin esto, Alpine no puede controlar la visibilidad de
        // los menús y quedan siempre abiertos. Si en el futuro se migra a la
        // build CSP-safe de Alpine (@alpinejs/csp), esto se puede quitar.
        $scriptSrc .= " 'unsafe-eval'";

        $response->headers->set(
            "default-src 'self'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'; object-src 'none'; "
            . "script-src {$scriptSrc}; "
            . "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.bunny.net; "
            . "font-src 'self' https://fonts.bunny.net data:; img-src 'self' data: blob: https:; "
            . "media-src 'self' blob: https:; frame-src 'self' https:; connect-src 'self'"
        );

        if ($request->user()) {
            $response->headers->set('Cache-Control', 'no-store, private');
            $response->headers->set('Pragma', 'no-cache');
        }

        if ($request->isSecure() && app()->isProduction()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
