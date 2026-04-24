<?php

declare(strict_types=1);

namespace App\Config;

final class Config
{
    private static array $items = [];

    public static function load(): void
    {
        if (!empty(self::$items)) {
            return;
        }

        self::$items = [
            'app' => require __DIR__ . '/app.php',
            'database' => require __DIR__ . '/database.php',
            'mail' => require __DIR__ . '/mail.php',
        ];
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        self::load();
        $segments = explode('.', $key);
        $value = self::$items;

        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }
}

