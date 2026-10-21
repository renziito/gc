<?php
declare(strict_types=1);
final class Normalizer {
    public static function json(mixed $v): string {
        $s = json_encode($v, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_SUBSTITUTE);
        if ($s === false) throw new RuntimeException('JSON encode failed.');
        return $s;
    }
    public static function nullableString(mixed $v): ?string {
        return is_string($v) || is_numeric($v) ? (string)$v : null;
    }
    public static function utcDate(mixed $v): ?string {
        if (!is_string($v) || trim($v)==='') return null;
        try { $d = new DateTimeImmutable($v); return $d->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s'); }
        catch (Throwable) { return null; }
    }
    public static function upsertList(PDO $pdo, string $table, array $items, string $key, callable $map): int {
        $n = 0;
        foreach ($items as $item) {
            if (!is_array($item) || !array_key_exists($key, $item)) continue;
            $map($item); $n++;
        }
        return $n;
    }
}
