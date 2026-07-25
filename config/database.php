<?php

declare(strict_types=1);

/**
 * Configuración de la base de datos.
 *
 * En producción se recomiendan variables de entorno. Los hostings compartidos
 * que no las admitan pueden usar config/database.local.php (ignorado por Git).
 */
function database_config(): array
{
    $config = [
        'host' => getenv('DB_HOST') ?: 'db',
        'port' => getenv('DB_PORT') ?: '3306',
        'name' => getenv('DB_NAME') ?: 'dblibreria',
        'user' => getenv('DB_USER') ?: 'libreria',
        'password' => getenv('DB_PASSWORD') ?: 'libreria_segura',
        'charset' => 'utf8mb4',
    ];

    $localConfig = __DIR__ . '/database.local.php';

    if (is_file($localConfig)) {
        $overrides = require $localConfig;

        if (is_array($overrides)) {
            $config = array_merge($config, $overrides);
        }
    }

    return $config;
}

function db(): PDO
{
    static $connection = null;

    if ($connection instanceof PDO) {
        return $connection;
    }

    $config = database_config();
    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
        $config['host'],
        $config['port'],
        $config['name'],
        $config['charset']
    );

    $connection = new PDO(
        $dsn,
        (string) $config['user'],
        (string) $config['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
        ]
    );

    return $connection;
}
