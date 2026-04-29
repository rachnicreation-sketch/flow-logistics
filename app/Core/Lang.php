<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\Setting;

final class Lang
{
    private static ?array $translations = null;
    private static string $current = 'fr';

    public static function load(): void
    {
        $settings = (new Setting())->allSettings();
        self::$current = $settings['app_language'] ?? 'fr';

        $file = base_path("app/Lang/" . self::$current . ".php");
        if (file_exists($file)) {
            self::$translations = require $file;
        } else {
            self::$translations = [];
        }
    }

    public static function get(string $key, array $replace = []): string
    {
        if (self::$translations === null) {
            self::load();
        }

        $value = self::$translations[$key] ?? $key;

        foreach ($replace as $k => $v) {
            $value = str_replace(':' . $k, (string)$v, $value);
        }

        return $value;
    }
}
