<?php
declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::middleware([\ModuleMicroHybrid\Http\Middleware\ServiceApiIpValidator::class])->group(function () {
    Route::any('/apiservice/{serviceName}', function ($serviceName) {
        return response()->json(\ModuleMicroHybrid\Api::resolveIncoming($serviceName, (json_decode(request()->getContent(), true) ?? [])))->setEncodingOptions(JSON_UNESCAPED_UNICODE | JSON_HEX_AMP);;
    })->where('serviceName', '[a-zA-Z0-9\/\_]+');
});
