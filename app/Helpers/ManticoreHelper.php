<?php

namespace App\Helpers;

use PDO;
use PDOException;

class ManticoreHelper
{
    private static $connection = null;

    public static function getConnection()
    {
        if (self::$connection === null) {
            $host = config('database.connections.manticore.host', 'manticore');
            $port = config('database.connections.manticore.port', 9306);

            $dsn = "mysql:host={$host};port={$port}";

            try {
                self::$connection = new PDO($dsn, '', '', [
                    PDO::ATTR_TIMEOUT => 5,
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]);
            } catch (PDOException $e) {
                throw new \Exception("Manticore connection failed: " . $e->getMessage());
            }
        }

        return self::$connection;
    }

    public static function select($query, $bindings = [])
    {
        $pdo = self::getConnection();

        $stmt = $pdo->prepare($query);
        $stmt->execute($bindings);

        return $stmt->fetchAll();
    }

    public static function query($sql, $params = [])
    {
        $pdo = self::getConnection();

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public static function execute($sql, $params = [])
    {
        $pdo = self::getConnection();

        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public static function search($index, $query, $limit = 10)
    {
        $sql = "
            SELECT *, WEIGHT() as weight
            FROM {$index}
            WHERE MATCH(?)
            ORDER BY WEIGHT() DESC
            LIMIT {$limit}
        ";

        return self::query($sql, [$query]);
    }
}
