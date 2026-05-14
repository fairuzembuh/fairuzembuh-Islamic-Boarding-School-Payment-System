<?php
// KONFIGURASI DATABASE
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'bendahara');

define('BASE_URL',   'http://localhost/bendahara/');
define('UPLOAD_DIR', __DIR__ . '/../uploads/');

/**
 * Class Database
 * Mengelola koneksi ke database (Singleton)
 */
class Database {
    private static ?Database $instance = null;
    private mysqli $conn;

    private function __construct() {
        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($this->conn->connect_error) {
            die("Koneksi gagal: " . $this->conn->connect_error);
        }
        $this->conn->set_charset("utf8mb4");
    }

    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection(): mysqli {
        return $this->conn;
    }

    public function escape(string $str): string {
        return $this->conn->real_escape_string($str);
    }

    public function query(string $sql): mysqli_result|bool {
        return $this->conn->query($sql);
    }

    public function getInsertId(): int {
        return $this->conn->insert_id;
    }

    public function close(): void {
        $this->conn->close();
        self::$instance = null;
    }
}
