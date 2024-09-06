<?php
require_once '../config/conexion.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

if (!$user_id) {
    header("Location: ../vista/login.php");
    exit();
}

$rating = isset($_POST['rating']) ? intval($_POST['rating']) : null;
$comentario = isset($_POST['comentario']) ? trim($_POST['comentario']) : '';
$idValorado = isset($_POST['idValorado']) ? intval($_POST['idValorado']) : null;

if (!$rating || !$idValorado) {
    header("Location: ../vista/error.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Verificar si el usuario ya ha valorado
$stmt = $db->prepare("SELECT COUNT(*) as total FROM valoraciones WHERE idValorado = ? AND idValorador = ?");
$stmt->execute([$idValorado, $user_id]);
$user_has_rated = $stmt->fetchColumn() > 0;

if ($user_has_rated) {
    header("Location: ../vista/error.php?mensaje=Ya has valorado a este usuario");
    exit();
}

$stmt = $db->prepare("INSERT INTO valoraciones (idValorado, idValorador, valoracion, comentario) VALUES (?, ?, ?, ?)");
$stmt->execute([$idValorado, $user_id, $rating, $comentario]);

header("Location: ../vista/todos.php");
exit();
?>
