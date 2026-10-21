<?php
declare(strict_types=1);
return [
    'app_env' => 'production',
    'app_key' => 'REPLACE_WITH_32_PLUS_RANDOM_CHARACTERS',
    'database' => [
        'host' => 'localhost',
        'name' => 'ynrttgii_gc',
        'user' => 'ynrttgii_gc_user',
        'password' => 'Sarabia2823',
        'charset' => 'utf8mb4',
    ],
    'http' => [
        'connect_timeout_seconds' => 5,
        'timeout_seconds' => 20,
        'max_response_bytes' => 5000000,
        'user_agent' => 'GalacticCommand/0.1 (unofficial Helldivers 2 companion)',
    ],
    'cron_token' => 'REPLACE_WITH_A_LONG_RANDOM_TOKEN',
    'push' => [
        'public_key' => 'BDYThp-pzxI12mJip1_1_C-slTjRvQ7TwvnHgnd-DDt9dJKJWhzFMz0yFU20arwtCHQsZcfNpjcSvkAFDFBYAkc',
        'private_key' => 'ztkRT9niT62nLUpB2yuXuSCol9_OuSg2qfjx6CvKF8k',
        'subject' => 'sepia.aki@gmail.com',
    ],
    'storage_path' => dirname(__DIR__) . '/storage',
];
