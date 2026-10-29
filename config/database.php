<?php
// config/database.php : connexion unique à MySQL (singleton PDO)

const DB_DSN  = 'mysql:host=127.0.0.1;dbname=petsitter;charset=utf8mb4';
const DB_USER = 'root';
const DB_PASS = '';   // XAMPP : vide par défaut

class Database
{
    private static ?PDO $instance = null;

    private function __construct() {}

    public static function get(): PDO
    {
        if (self::$instance === null) {
            self::$instance = new PDO(DB_DSN, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        }
        return self::$instance;
    }
}
