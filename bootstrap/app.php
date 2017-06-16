<?php

require_once __DIR__.'/../vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| Here we will create the application instance that serves as the central
| piece of this framework. We'll use this application as an "IoC" container
|
*/
$konsole = new Konsole\Application(
    realpath(__DIR__.'/../')
);

/*
|--------------------------------------------------------------------------
| Register any command
|--------------------------------------------------------------------------
|
| You may define your own commands here if you don't want to add them in
| config/app.php file.
|
*/
$konsole->registerCommand([
    //
]);

return $konsole;
