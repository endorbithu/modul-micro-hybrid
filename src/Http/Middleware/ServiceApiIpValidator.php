<?php

declare(strict_types=1);

namespace EndorbitHu\ModuleMicroHybrid\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ServiceApiIpValidator
{

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
      if(empty(config('module-micro-hybrid.ip_filter'))) {
          return $next($request);
      }
      
        foreach (config('module-micro-hybrid.allowed_ips', []) as $allowedIp) {
            $allowedIp = trim($allowedIp);
            if ($allowedIp === '*' || str_starts_with($request->ip(), $allowedIp)) {
                return $next($request);
            }
        }

        abort(403, 'Ip address has not set!');
    }
}
