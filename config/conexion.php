<?php
class Database {
    private $host = 'db5015909185.hosting-data.io';
    private $port = '3306';  // Agregar el puerto de la base de datos
    private $db_name = 'dbs12966751';
    private $username = 'dbu3832332';
    private $password = 'Temporal1_2024!';
    private $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            error_log("Error de conexión: " . $exception->getMessage());
            echo "Error de conexión: " . $exception->getMessage();
        }

        return $this->conn;
    }
}

// Crear una instancia de la clase Database y obtener la conexión
$database = new Database();
$conn = $database->getConnection();
?>
