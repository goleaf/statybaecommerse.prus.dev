<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class AddSecurityHeaders
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $headers = [
            'X-Frame-Options' => config('security.headers.x_frame_options', 'DENY'),
            'X-Content-Type-Options' => config('security.headers.x_content_type_options', 'nosniff'),
            'Referrer-Policy' => config('security.headers.referrer_policy', 'strict-origin-when-cross-origin'),
            'Permissions-Policy' => config('security.headers.permissions_policy', "geolocation=(), microphone=(), camera=(), payment=(), usb=()"),
            'Content-Security-Policy-Report-Only' => config('security.headers.csp_report_only', "default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self' data:; connect-src 'self'; frame-ancestors 'self'; form-action 'self'; base-uri 'self';"),
        ];

        foreach ($headers as $header => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            if (! $response->headers->has($header)) {
                $response->headers->set($header, $value);
            }
        }

        return $response;
    }
}
