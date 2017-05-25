<?php

require_once __DIR__ . '/../vendor/autoload.php';

try{
    (new Dotenv\Dotenv(__DIR__ . '/../'))->load();
}catch (Dotenv\Exception\InvalidPathException $e){
    //
}

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

$app->withEloquent();

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
    ALS\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    ALS\Console\Kernel::class
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
    'auth' => \ALS\Http\Middleware\Authenticate::class,
    'role' => \ALS\Core\Authorization\Middleware\RoleMiddleware::class
]);

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
$app->register(\ALS\Providers\AppServiceProvider::class);
$app->register(\ALS\Providers\AuthServiceProvider::class);
$app->register(\ALS\Core\Authorization\AuthorizationServiceProvider::class);
$app->register(\ALS\Modules\User\Providers\ModuleServiceProvider::class);
$app->register(\ALS\Modules\Option\Providers\ModuleServiceProvider::class);
$app->register(\ALS\Modules\Shipment\Providers\ModuleServiceProvider::class);
$app->register(Prettus\Repository\Providers\LumenRepositoryServiceProvider::class);

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

$app->group(['namespace' => 'App\Http\Controllers'], function ($app){
    require __DIR__ . '/../routes/web.php';
});

return $app;
