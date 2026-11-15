<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Pembaruan aplikasi
    |--------------------------------------------------------------------------
    |
    | Tombol pembaruan hanya tersedia untuk admin. Proses update menjalankan
    | git pull, composer install, migrasi database, build aset, dan pembersihan
    | cache. Matikan fitur ini bila instalasi dikelola sepenuhnya lewat SSH.
    |
    */

    'enabled' => (bool) env('APP_UPDATE_ENABLED', true),

    'repository' => env('APP_UPDATE_REPOSITORY') ?: 'reeve-ui/orcc',

    'branch' => env('APP_UPDATE_BRANCH') ?: 'main',

    'build_assets' => (bool) env('APP_UPDATE_BUILD_ASSETS', true),

    'timeout' => max(60, (int) env('APP_UPDATE_TIMEOUT', 900)),

    'php_binary' => env('APP_UPDATE_PHP_BINARY') ?: (PHP_SAPI === 'cli' ? PHP_BINARY : 'php'),

    'git_binary' => env('APP_UPDATE_GIT_BINARY') ?: 'git',

    'composer_binary' => env('APP_UPDATE_COMPOSER_BINARY') ?: 'composer',

    'npm_binary' => env('APP_UPDATE_NPM_BINARY') ?: 'npm',

];
