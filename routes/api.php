<?php
declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::middleware([\EndorbitHu\ModuleMicroHybrid\Http\Middleware\ServiceApiIpValidator::class])->group(function () {
    Route::any('/apiservice/{moduleAndMethod}', function ($moduleAndMethod) {
        return response()->json(\EndorbitHu\ModuleMicroHybrid\Api::resolveIncoming($moduleAndMethod, (json_decode(request()->getContent(), true) ?? [])))->setEncodingOptions(JSON_UNESCAPED_UNICODE | JSON_HEX_AMP);;
    })->where('moduleAndMethod', '[a-zA-Z0-9\/\_]+');
});
