<?php
declare(strict_types=1);
return [
    'app_env' => 'production',
    'app_key' => 'REPLACE_WITH_32_PLUS_RANDOM_CHARACTERS',
    'database' => [
        'host' => 'localhost',
        'name' => 'CPANEL_DB_NAME',
        'user' => 'CPANEL_DB_USER',
        'password' => 'REPLACE_ME',
        'charset' => 'utf8mb4',
    ],
    'http' => [
        'connect_timeout_seconds' => 5,
        'timeout_seconds' => 20,
        'max_response_bytes' => 5000000,
        'user_agent' => 'GalacticCommand/0.1 (unofficial Helldivers 2 companion)',
    ],
    'cron_token' => 'REPLACE_WITH_A_LONG_RANDOM_TOKEN',
    'storage_path' => dirname(__DIR__) . '/storage',
];
