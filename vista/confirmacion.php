<?php
session_start();
require_once '../config/conexion.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['user_id'];

$database = new Database();
$conn = $database->getConnection();

// Obtener detalles del usuario
$query = "SELECT * FROM Usuarios WHERE idUsuario = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo 'Usuario no encontrado.';
    exit();
}

include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Registro</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <div class="container py-5">
        <h2 class="text-center mb-4">Confirmación de Registro</h2>
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Registro Completado</h4>
                        <p>¡Gracias por registrarte, <?php echo htmlspecialchars($user['nombreUsuario']); ?>!</p>
                        <p>Tu suscripción ha sido pagada con éxito.</p>
                        <a href="principal.php" class="btn btn-primary btn-block">Ir al Inicio</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

<?php include '../includes/footer.php'; ?>
