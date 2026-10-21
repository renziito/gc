<?php
declare(strict_types=1);

final class UpstreamClient
{
    private array $cfg;

    public function __construct(array $cfg)
    {
        $this->cfg = $cfg;
    }

    public function getJson(string $url, array $headers): array
    {
        if (!function_exists('curl_init')) {
            throw new RuntimeException('PHP cURL extension is required.');
        }

        $body = '';
        $responseHeaders = [];
        $limit = (int) ($this->cfg['max_response_bytes'] ?? 5000000);

        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT => (int) ($this->cfg['connect_timeout_seconds'] ?? 5),
            CURLOPT_TIMEOUT => (int) ($this->cfg['timeout_seconds'] ?? 20),
            CURLOPT_USERAGENT => (string) ($this->cfg['user_agent'] ?? 'GalacticCommand/0.1'),
            CURLOPT_HTTPHEADER => $headers,

            CURLOPT_HEADERFUNCTION => static function ($curl, string $line) use (&$responseHeaders): int {
                $length = strlen($line);
                $parts = explode(':', $line, 2);

                if (count($parts) === 2) {
                    $responseHeaders[strtolower(trim($parts[0]))] = trim($parts[1]);
                }

                return $length;
            },

            CURLOPT_WRITEFUNCTION => static function ($curl, string $chunk) use (&$body, $limit): int {
                $length = strlen($chunk);

                if (strlen($body) + $length > $limit) {
                    return 0;
                }

                $body .= $chunk;

                return $length;
            },
        ]);

        $start = microtime(true);
        $result = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        if ($result === false) {
            throw new RuntimeException(
                'Upstream transport failed: ' . ($error ?: 'unknown error')
            );
        }

        if ($status < 200 || $status >= 300) {
            throw new RuntimeException('Upstream HTTP ' . $status);
        }

        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException(
                'Invalid upstream JSON: ' . json_last_error_msg()
            );
        }

        return [
            'status' => $status,
            'data' => $data,
            'raw' => $body,
            'duration_ms' => (int) round((microtime(true) - $start) * 1000),
            'headers' => $responseHeaders,
        ];
    }
}