<?php

require_once __DIR__ . '/../../vendor/autoload.php';

(new Laravel\Lumen\Bootstrap\LoadEnvironmentVariables(
    dirname(__DIR__)
))->bootstrap();

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| Here we will load the environment and create the application instance
| that serves as the central piece of this framework. We'll use this
| application as an "IoC" container and router for this framework.
|
*/

$app = new Laravel\Lumen\Application(
    realpath(__DIR__ . '/../')
);

$app->withFacades();

// $app->withEloquent();

$configFiles = ['app', 'phystrix', 'security', 'clientapis'];

foreach ($configFiles as $configFile) {
    $app->configure($configFile);
}

/*
|--------------------------------------------------------------------------
| Register Container Bindings
|--------------------------------------------------------------------------
|
| Now we will register a few bindings in the service container. We will
| register the exception handler and the console kernel. You may add
| your own bindings here if you like or you can make another file.
|
*/

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

/*
|--------------------------------------------------------------------------
| Register Middleware
|--------------------------------------------------------------------------
|
| Next, we will register the middleware with the application. These can
| be global middleware that run before and after each request into a
| route or middleware that'll be assigned to some specific routes.
|
*/
$app->routeMiddleware([
    'GenerateCorrelationId' => ProBillerNG\Base\LumenMiddleware\GenerateCorrelationId::class,
    'GenerateSessionId'     => \App\Http\Middleware\GenerateSessionId::class,
    'ValidateSessionId'     => \App\Http\Middleware\ValidateSessionId::class,
    'NGLogger'              => App\Http\Middleware\NGLogger::class,
    'XApiKeyAuth'           => ProBillerNG\Base\LumenMiddleware\XApiKeyAuth::class,
]);


// $app->routeMiddleware([
//     'auth' => Lumen\Http\Middleware\Authenticate::class,
// ]);

/*
|--------------------------------------------------------------------------
| Register Service Providers
|--------------------------------------------------------------------------
|
| Here we will register all of the application's service providers which
| are used to bind services into the container. Service providers are
| totally optional, so you are not required to uncomment this line.
|
*/

$app->register(\App\Providers\TransactionServiceProvider::class);
$app->register(\App\Providers\RocketgateServiceProvider::class);
$app->register(\App\Providers\NetbillingServiceProvider::class);
$app->register(\App\Providers\PumaPayServiceProvider::class);
$app->register(\App\Providers\EpochPayServiceProvider::class);
$app->register(\App\Providers\QyssoServiceProvider::class);
$app->register(\App\Providers\LegacyServiceProvider::class);
$app->register(\App\Providers\ConfigServiceProvider::class);
// $app->register(Lumen\Providers\AuthServiceProvider::class);
// $app->register(Lumen\Providers\EventServiceProvider::class);
$app->register(\App\Providers\ValidationServiceProvider::class);

/*
|--------------------------------------------------------------------------
| Load The Application Routes
|--------------------------------------------------------------------------
|
| Next we will include the routes file so that they can all be added to
| the application. This will provide all of the URLs the application
| can respond to, as well as the controllers that may handle them.
|
*/

$app->router->group([
    'namespace' => 'ProBillerNG\Transaction\UI\Http\Controllers',
], function ($router) {
    require __DIR__ . '/../routes/routes.php';
});

// Required for detailed exception responses
$app->translator->setLocale('en');

return $app;
