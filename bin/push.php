<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__) . '/app/bootstrap.php';
$vendor = dirname(__DIR__) . '/vendor/autoload.php';
if (!is_file($vendor)) { fwrite(STDERR, "Install dependencies with composer install first.\n"); exit(2); }
require $vendor;
require_once dirname(__DIR__) . '/app/PushNotifier.php';
$options = getopt('', ['title:', 'body:', 'url::']);
$title = trim((string)($options['title'] ?? 'Galactic Command'));
$body = trim((string)($options['body'] ?? 'New operational intelligence is available.'));
$url = (string)($options['url'] ?? '/');
try {
    $result = (new PushNotifier($pdo, $appConfig))->send($title, $body, $url);
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
} catch (Throwable $e) {
    fwrite(STDERR, $e->getMessage() . PHP_EOL);
    exit(1);
}
