<?php

declare(strict_types=1);

class AppConfig
{
    private static bool $envLoaded = false;

    private static function loadEnvFile(): void
    {
        if (self::$envLoaded) {
            return;
        }

        self::$envLoaded = true;

        $envPath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . '.env';
        if (!is_file($envPath) || !is_readable($envPath)) {
            return;
        }

        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '' || str_starts_with($trimmed, '#')) {
                continue;
            }

            $parts = explode('=', $trimmed, 2);
            if (count($parts) !== 2) {
                continue;
            }

            $name = trim($parts[0]);
            $value = trim($parts[1]);

            if ($name === '') {
                continue;
            }

            if (
                (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                (str_starts_with($value, "'") && str_ends_with($value, "'"))
            ) {
                $value = substr($value, 1, -1);
            }

            if (getenv($name) === false) {
                putenv($name . '=' . $value);
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }

    public static function nutritionApiKey(): string
    {
        self::loadEnvFile();
        $value = getenv('USDA_API_KEY');
        return $value !== false ? $value : 'YOUR_USDA_API_KEY';
    }
}
