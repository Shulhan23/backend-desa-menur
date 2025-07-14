<?php

namespace App\Http\Middleware;

use Illuminate\Routing\Middleware\ThrottleRequests;

class SkipThrottle extends ThrottleRequests
{
    protected function resolveRequestSignature($request)
    {
        return null; // Bypass rate limit
    }
}
