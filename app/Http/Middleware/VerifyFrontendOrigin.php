<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyFrontendOrigin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */

    public function handle($request, Closure $next)
    {
        $origin = $request->headers->get('origin');

        $allowedOrigins = [
            'http://localhost:3000',
            'https://desamenur.com',
            'https://admin.desamenur.com',
        ];

        // Jika origin null, izinkan hanya dari localhost (proxy internal)
        if (is_null($origin)) {
            if ($request->ip() !== '127.0.0.1' && $request->ip() !== '::1') {
                return response()->json(['message' => 'Origin null not allowed'], 403);
            }
        } elseif (!in_array($origin, $allowedOrigins)) {
            return response()->json(['message' => 'Origin not allowed'], 403);
        }

        return $next($request);
    }

}
