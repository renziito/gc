<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require dirname(__DIR__) . '/app/bootstrap.php';

$source = null;
$id32 = null;
$due = false;

foreach ($argv as $arg) {
    if (strpos($arg, '--source=') === 0) {
        $source = substr($arg, 9);
    } elseif (strpos($arg, '--id32=') === 0) {
        $id32 = (int) substr($arg, 7);
    } elseif ($arg === '--due') {
        $due = true;
    }
}

$service = new SyncService(
    $pdo,
    new UpstreamClient($appConfig['http']),
    $sourceConfig
);

$run = function (string $key, ?int $entity = null) use ($service): bool {
    try {
        $result = $service->sync($key, $entity);

        echo json_encode(
            $result,
            JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
        ) . PHP_EOL;

        return true;
    } catch (Throwable $e) {
        fwrite(STDERR, '[' . $key . '] ' . $e->getMessage() . PHP_EOL);
        return false;
    }
};

if ($due) {
    $routes = $sourceConfig['community_api']['routes'];
    $requestsMade = 0;

    foreach ($routes as $key => $route) {
        // Detail endpoints require an explicit station ID32.
        if (!empty($route['requires_id32'])) {
            continue;
        }

        $interval = max(60, (int) ($route['interval_seconds'] ?? 300));

        // Check the most recent attempt, not only successful attempts.
        $stmt = $pdo->prepare(
            'SELECT started_at
             FROM source_sync_runs
             WHERE source_key = ?
             ORDER BY id DESC
             LIMIT 1'
        );
        $stmt->execute([$key]);
        $lastAttempt = $stmt->fetchColumn();

        if ($lastAttempt !== false && $lastAttempt !== null) {
            $lastTime = strtotime((string) $lastAttempt . ' UTC');

            if ($lastTime !== false && $lastTime + $interval > time()) {
                echo '[' . $key . '] Skipped: interval not elapsed.'
                    . PHP_EOL;
                continue;
            }
        }

        // Avoid sending all requests in a burst.
        if ($requestsMade > 0) {
            sleep(3);
        }

        $run($key);
        $requestsMade++;
    }

    echo 'Sync due completed. Sources attempted: '
        . $requestsMade . PHP_EOL;

    exit(0);
}

if (!$source) {
    fwrite(
        STDERR,
        'Usage: php bin/sync.php --source=war '
        . '[--id32=749875195] OR --due' . PHP_EOL
    );
    exit(2);
}

$run($source, $id32);
