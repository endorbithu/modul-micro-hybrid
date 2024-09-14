<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2022. 03. 31.
 * Time: 14:47
 */

namespace DelocalBase\Http\Middleware;

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
        foreach (config('delocalbase.service_api_ips', []) as $allowedIp) {
            $allowedIp = trim($allowedIp);
            if ($allowedIp === '*' || str_starts_with($request->ip(), $allowedIp)) {
                return $next($request);
            }
        }

        abort(403, 'Ip address has not set (config: delocalbase)');
    }
}
