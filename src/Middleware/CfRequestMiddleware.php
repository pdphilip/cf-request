<?php

namespace PDPhilip\CfRequest\Middleware;

use Closure;
use Illuminate\Http\Request;
use PDPhilip\CfRequest\CfRequest;

class CfRequestMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $cfRequest = CfRequest::createFrom($request);
        app()->instance('request', $cfRequest);

        return $next($cfRequest);
    }
}
