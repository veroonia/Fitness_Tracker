<?php

declare(strict_types=1);

class Database
{
    private const DB_HOST = '127.0.0.1';
    private const DB_PORT = 3306;
    private const DB_NAME = 'fitness_tracker';
    private const DB_USER = 'root';
    private const DB_PASS = '';

    private static ?PDO $pdo = null;
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

    private static function env(string $name, string $default): string
    {
        $value = getenv($name);
        return $value !== false ? trim($value) : $default;
    }

    public static function connection(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        self::loadEnvFile();

        $dbName = self::env('DB_NAME', self::DB_NAME);
        $dbUser = self::env('DB_USER', self::DB_USER);
        $dbPass = self::env('DB_PASS', self::DB_PASS);
        $dbPort = (int)self::env('DB_PORT', (string)self::DB_PORT);
        $primaryHost = self::env('DB_HOST', self::DB_HOST);

        $hosts = [$primaryHost];
        if ($primaryHost !== 'localhost') {
            $hosts[] = 'localhost';
        }
        if ($primaryHost !== '127.0.0.1') {
            $hosts[] = '127.0.0.1';
        }

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_TIMEOUT => 3,
        ];

        $errors = [];

        foreach ($hosts as $host) {
            $dsn = 'mysql:host=' . $host . ';port=' . $dbPort . ';dbname=' . $dbName . ';charset=utf8mb4';

            try {
                self::$pdo = new PDO($dsn, $dbUser, $dbPass, $options);
                return self::$pdo;
            } catch (PDOException $exception) {
                $errors[] = $host . ':' . $dbPort . ' (' . $exception->getMessage() . ')';
            }
        }

        throw new RuntimeException(
            'Unable to connect to MySQL. Tried: ' . implode(', ', $errors) .
            '. Ensure MySQL is running in XAMPP and DB_HOST/DB_PORT in .env are correct.'
        );
    }
}
