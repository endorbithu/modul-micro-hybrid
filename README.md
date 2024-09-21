# Laravel module/microservice hybrid framework
Laravel Package for creating Laravel local module that can be separated from the project any time as indenpendent microservice with no implementation consequence.

## How to use

Our example project is a decentralized, modularized Laravel project. The modules have only one common dependency, the `endorbithu/module-micro-hybrid` package. 
```
app/
app_modules/
    ExampleModule/        
        src/            
            Providers/
                ServiceProvider.php
            Api.php
    OtherExampleModule        
        src/     
            Console/
                Commands
                    SandboxCommand.php       …            
            Providers/                
                ServiceProvider.php            
            Api.php
config/    
    module-micro-hybrid.php    
vendor/    
    endorbithu/        
        module-micro-hybrid/  
            routes/
                api.php          
            src/
                Http/
                    Middleware/
                        ServiceApiIpValidator.php  
                Providers/                
                    ServiceProvider.php                                         
                ModuleApi.php
```
### Communication with modules

Modules must be accessed via the `EndorbitHu\ModuleMicroHybrid\ModuleApi` class. The key point is that you must not reference the module directly by its namespace. Instead, call the module’s API class using this service container: `EndorbitHu\ModuleMicroHybrid\ModuleApi::make('OtherModule')`, ensuring there is no implementation dependency.

#### One implementation two scenarios:
```
namespace OtherExampleModule\Console\Commands;

use EndorbitHu\ModuleMicroHybrid\ModuleApi;

class SandboxCommand
{
    ...
    echo ModuleApi::make('ExampleModule')→sayHello('Linda');
    //Hello Linda!
    ...
}
```

#### 1. ExampleModule is part of the same Laravel project

Since ExampleModule is local, no remote host is specified for it in the `config/module-micro-hybrid.php` file:
```
  'service_namespace_hosts' => [      

   ],
```
In this case the resolvation will be a simple local reference via Laravel service container:
```
ModuleApi::make('ExampleModule')→sayHello('Linda');
```

==>

```
return App::make(\ExampleModule\Api::class)→sayHello('Linda')
```

#### 2. ExampleModule has been moved to the other host (hosted in: anotherproject.com) 

The app_modules/ExampleModule directory has been moved to another Laravel project on a different host. The host for this module has been set in the configuration (this config entry enables API communication):
```
  'service_namespace_host' => [      
     'ExampleModule' => 'https://anotherproject.com',  
   ],
```

```
ModuleApi::make('ExampleModule')→sayHello('Linda');
```

===>

```
//$serviceHost === 'https://anotherproject.com'
//$servicePath  === 'ExampleModule' 
//$methodName === 'sayHello'
//$arguments === ['Linda']


$response = Http::withBody(json_encode($arguments), 'application/json')
            ->get($serviceHost.'/apiservice/'.$servicePath.'/'.$methodName);
        
return $response->json(0);
```

Since the module-micro-hybrid package is also installed on the Laravel project at anotherproject.com, you don’t need to create an HTTP endpoint or handle anything manually. Just place ExampleModule under the \ExampleModule namespace, and the module-micro-hybrid package will manage incoming HTTP calls and the response:

```
Route::middleware([ServiceApiIpValidator::class])->group(function () {

    Route::any('/apiservice/{moduleAndMethod}', function ($moduleAndMethod) {

        $params = json_decode(request()->getContent(), true) ?? []));
        return response()
               ->json(ModuleApi::resolveIncoming($moduleAndMethod, $params));
       
    })->where('moduleAndMethod', '[a-zA-Z0-9\/\_]+');

});
```
The original project's module-micro-hybrid package will still return the same "Hello Linda!" string value, whether it comes from a remote API endpoint or a local module. This means you don’t need to modify SandboxCommand.php, even if you change the location of ExampleModule.  

Note: If the given microservice is not based on PHP/Laravel, the remote microservice can be any HTTP-aware service, regardless of the programming language. We can also use that via the module-micro-hybrid package. It simply needs to handle the {HOST}/apiservice/ExampleModule/getHello endpoint and return an appropriate HTTP response that can be processed by the `module-micro-hybrid` package in the root project.

## Contstraints
- A module's namespace must be a top-level namespace `ExampleModule\`

- You must not directly reference a module's namespace, even if it's accessible. Instead, always use `ModuleApi::make('ExampleModule')` service container to refer to the `ExampleModule\Api` class instance. 

- For calling methods like `ExampleModule\Api::anyMethod(arg1, arg2, ...)`, the method arguments must only be PHP scalars, null or data models/arrays containing only PHP scalars or null. This is because the parameters will be serialized into a JSON array in the HTTP request body.

- For return types like `ExampleModule\Api::anyMethod(...): string`, you must use only PHP scalars, null or data models/arrays containing only PHP scalars or null. The return value will be converted into a JSON string in the HTTP response body, as described above.

- If a database is necessary, you must use an independent database or a separated database segment (identified by a prefix) without any constraints on other parts of the database, so it can be easily migrated to another database.

## Protection

If you do not protect these service API endpoints (`{HOST}/apiservice/..`) at a lower layer, there is a solution for this in the package, as shown above:
```
Route::middleware([ServiceApiIpValidator::class])->group(function ()  ...
```
The ServiceApiIpValidator middleware is used to validate incoming requests based on IP. The IP whitelist can be configured in the config/module-micro-hybrid.php file.
```
'allowed_ips' => [
        '127.0.0.1', 
        '67.121.232.9', 
        '45.85.74.', 
        ...
],
```
