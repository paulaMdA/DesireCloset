<?php
session_start();
require_once '../config/conexion.php';

$response = ['success' => false];

if (isset($_SESSION['user_id']) && isset($_POST['idProducto']) && isset($_POST['action'])) {
    $database = new Database();
    $conn = $database->getConnection();

    $idProducto = $_POST['idProducto'];
    $idUsuario = $_SESSION['user_id'];
    $action = $_POST['action'];

    if ($action == 'like') {
        // Agregar "Me Gusta"
        $stmt = $conn->prepare("INSERT INTO megusta (idProducto, idUsuario) VALUES (?, ?)");
        $stmt->execute([$idProducto, $idUsuario]);
    } else if ($action == 'unlike') {
        // Quitar "Me Gusta"
        $stmt = $conn->prepare("DELETE FROM megusta WHERE idProducto = ? AND idUsuario = ?");
        $stmt->execute([$idProducto, $idUsuario]);
    }

    $response['success'] = true;
}

header('Content-Type: application/json');
echo json_encode($response);
?>
