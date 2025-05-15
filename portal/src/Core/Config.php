<?php
namespace App\Core;

class Config
{
    private static array $data = [];

    public static function load(string $file): array
    {
        if (!file_exists($file)) {
            throw new \RuntimeException("Config file not found: $file");
        }
        self::$data = require $file;
        return self::$data;
    }

    public static function get(string $key, $default = null)
    {
        return self::$data[$key] ?? $default;
    }
}
