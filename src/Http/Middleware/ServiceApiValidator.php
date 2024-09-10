<?php

namespace ModuleMicroHybrid\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
      
        foreach (config('module-micro-hybrid.service_api_hosts', []) as $allowedIp) {
            $allowedIp = trim($allowedIp);
            if ($allowedIp === '*' || str_starts_with($request->ip(), $allowedIp)) {
                return $next($request);
            }
        }

        abort(403, 'Ip address has not set (config: module-micro-hybrid)');
    }
}
