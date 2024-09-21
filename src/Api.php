<?php
declare(strict_types=1);

namespace EndorbitHu\ModuleMicroHybrid;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


class Api
{
    public static function make(string $service): object
    {
        if (strpos($service, '\\') === false) {
            $service = $service . '\\Api';
        }

        $serviceExp = explode('\\', $service);
        if (self::getNamespacesApiEndpoint($serviceExp[0])) {
            return new Api($service);
        } else {
            return App::make($service);
        }
    }

    public static function resolveIncoming(string $serviceAndMethod, ?array $params): array
    {
        try {
            $serviceAndMethodExpl = explode('/', $serviceAndMethod);
            $method = array_pop($serviceAndMethodExpl);
            $service = (implode('\\', $serviceAndMethodExpl));

            if (strpos($service, '\\') === false) {
                $service = $service . '\\Api';
            }

            $service = App::make($service);
            $params = $params ?? [];
            $return = [call_user_func_array([$service, $method], array_values($params))];
            return $return;
        } catch (\Throwable $e) {
            $errorId = 'ERROR ID: ' . ((uniqid(date('Ymd_His_'))) . substr(md5($e->__toString()), 0, 3));
            Log::error($errorId . PHP_EOL . $e);
            return ['exception' => $errorId . PHP_EOL . $e->getMessage() . ' ' . $e->getFile() . ':' . $e->getLine()];
        }
    }

  
    public static function getNamespacesApiEndpoint(string $namespace = ''): null|array|string
    {
        $eps = config('module-micro-hybrid.service_namespace_hosts', []);

        if ($namespace === '') return $eps;

        return $eps[$namespace] ?? null;
    }

    protected ?string $endPointUrl = null;
    protected ?string $servicePath = null;

    protected function __construct(protected string $serviceName)
    {
        $urlExpl = explode('\\', $this->serviceName);
        $this->endPointUrl = rtrim(self::getNamespacesApiEndpoint($urlExpl[0]), '/');
        $this->servicePath = implode('/', $urlExpl);
    }

    public function __call(string $methodName, array $arguments): mixed
    {
        $finalUrl = $this->endPointUrl . '/apiservice/' . $this->servicePath . '/' . $methodName;
        $response = Http::withBody(json_encode($arguments), 'application/json')
            ->get($finalUrl);
        if ($response->json('exception')) {
            throw new \Exception($finalUrl . ' ' . $response->json('exception'));
        }

        return $response->json(0);
    }
}
