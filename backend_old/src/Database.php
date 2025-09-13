<?php
// backend/src/Database.php

class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        // Cargar variables de entorno (simulado, idealmente usar una librería como Dotenv)
        $env = parse_ini_file(__DIR__ . '/../.env');

        $host = $env['DB_HOST'] ?? 'localhost';
        $db   = $env['DB_NAME'] ?? 'fans_cooperativa';
        $user = $env['DB_USER'] ?? 'root';
        $pass = $env['DB_PASS'] ?? '';
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->conn = new PDO($dsn, $user, $pass, $options);
        } catch (\PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            die("Could not connect to the database. Check logs for details.");
        }
    }

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->conn;
    }
}