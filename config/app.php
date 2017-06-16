<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application name
    |--------------------------------------------------------------------------
    |
    | You may define your own application name here. Think about cool name like
    | 'Sven', 'Kunkka', 'Tidehunter', etc.
    |
    */
    'name' => 'Konsole',

    /*
    |--------------------------------------------------------------------------
    | Application version
    |--------------------------------------------------------------------------
    |
    | You may define your own application version here. Use valid version
    | which comply with semver see more here http://semver.org/.
    |
    */
    'version' => '2.0.0',

    /*
    |--------------------------------------------------------------------------
    | Available application commands
    |--------------------------------------------------------------------------
    |
    | You may define any avaliable commands of your own application here.
    |
    */
    'commands' => [
        Konsole\Commands\GenerateCommand::class,
        Konsole\Commands\BenchmarkCommand::class,
    ],

];
