<?php

declare(strict_types=1);

namespace App\Core;

final class RateLimiter
{
    public static function tooManyAttempts(string $key, int $maxAttempts, int $decaySeconds): bool
    {
        $record = self::get($key);
        if ($record === null) {
            return false;
        }

        $now = time();
        if (($record['expires_at'] ?? 0) <= $now) {
            self::clear($key);
            return false;
        }

        return (int) ($record['attempts'] ?? 0) >= $maxAttempts;
    }

    public static function hit(string $key, int $decaySeconds): int
    {
        $record = self::get($key);
        $now = time();

        if ($record === null || ($record['expires_at'] ?? 0) <= $now) {
            $record = [
                'attempts' => 1,
                'expires_at' => $now + $decaySeconds,
            ];
            self::put($key, $record);
            return 1;
        }

        $record['attempts'] = (int) ($record['attempts'] ?? 0) + 1;
        self::put($key, $record);
        return (int) $record['attempts'];
    }

    public static function clear(string $key): void
    {
        $path = self::pathFor($key);
        if (is_file($path)) {
            @unlink($path);
        }
    }

    public static function availableIn(string $key): int
    {
        $record = self::get($key);
        if ($record === null) {
            return 0;
        }

        $seconds = (int) ($record['expires_at'] ?? 0) - time();
        return max(0, $seconds);
    }

    private static function get(string $key): ?array
    {
        $path = self::pathFor($key);
        if (!is_file($path)) {
            return null;
        }

        $raw = @file_get_contents($path);
        if ($raw === false || trim($raw) === '') {
            return null;
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : null;
    }

    private static function put(string $key, array $record): void
    {
        $dir = self::directory();
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }

        @file_put_contents(self::pathFor($key), json_encode($record));
    }

    private static function pathFor(string $key): string
    {
        return self::directory() . DIRECTORY_SEPARATOR . hash('sha256', $key) . '.json';
    }

    private static function directory(): string
    {
        return project_path('logs/rate_limits');
    }
}

