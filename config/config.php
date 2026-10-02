<?php

/*
 * Application configuration
 */

return [
    'app' => [
        'name'    => 'IT Inventory System',
        'env'     => 'development',
        'debug'   => true,
        'version' => '1.0.0',
        'timezone'=> 'Asia/Jakarta',
    ],

    'database' => [
        'driver'    => 'mysql',
        'host'      => getenv('INVIT_DB_HOST') ?: '127.0.0.1',
        'port'      => getenv('INVIT_DB_PORT') ?: '3306',
        'database'  => getenv('INVIT_DB_NAME') ?: 'invit',
        'username'  => getenv('INVIT_DB_USER') ?: 'root',
        'password'  => getenv('INVIT_DB_PASS') ?: '',
        'charset'   => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
    ],

    'session' => [
        'name'   => 'invit_session',
        'lifetime' => 28800,
        'secure'   => false,
    ],

    'paths' => [
        'base'   => __DIR__ . '/..',
        'app'    => __DIR__ . '/../app',
        'views'  => __DIR__ . '/../views',
        'public' => __DIR__ . '/../public',
        'storage'=> __DIR__ . '/../storage',
        'uploads'=> __DIR__ . '/../public/uploads',
    ],

    // If the application is mounted at a sub-path under a virtual host,
    // adjust the base URL here, e.g. '/invIT'. When empty the URL is
    // auto-detected so it works with Laragon vhosts and htdocs alike.
    'url' => [
        'base' => '',
    ],
];