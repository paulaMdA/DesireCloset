<?php
session_start();
require_once '../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    $idUsuario = $_SESSION['idUsuario'];

    // Verificar que las contraseñas coincidan
    if ($newPassword !== $confirmPassword) {
        $mensaje = "Las contraseñas no coinciden.";
        header("Location: establecer_contrasena.php?mensaje=" . urlencode($mensaje));
        exit();
    }

    // Conectar a la base de datos
    $database = new Database();
    $conn = $database->getConnection();

    // Actualizar la contraseña en la base de datos
    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
    $query = "UPDATE usuarios SET password = :password WHERE idUsuario = :idUsuario";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':password', $hashedPassword);
    $stmt->bindParam(':idUsuario', $idUsuario);
    $stmt->execute();

    // Destruir la sesión
    session_destroy();

    $mensaje = "Contraseña actualizada exitosamente. Ahora puedes iniciar sesión con tu nueva contraseña.";
    header("Location: login.php?mensaje=" . urlencode($mensaje));
    exit();
}

// Verificar que el usuario esté en sesión
if (!isset($_SESSION['idUsuario'])) {
    header("Location: recuperar.php");
    exit();
}

include '../includes/header.php';
?>

<section class="establecer_contrasena container py-5">
    <h2 class="text-center mb-4">Establecer Nueva Contraseña</h2>
    <?php if (isset($_GET['mensaje'])): ?>
        <div class="alert alert-danger text-center"><?php echo $_GET['mensaje']; ?></div>
    <?php endif; ?>
    <form id="establecerContrasenaForm" action="establecer_contrasena.php" method="post" class="needs-validation" novalidate>
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label for="new_password" class="form-label">Nueva Contraseña</label>
                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                    <div class="invalid-feedback">Por favor, ingrese su nueva contraseña.</div>
                </div>
                <div class="form-group mb-3">
                    <label for="confirm_password" class="form-label">Confirmar Nueva Contraseña</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    <div class="invalid-feedback">Por favor, confirme su nueva contraseña.</div>
                </div>
                <button type="submit" class="btn btn-danger btn-block">Establecer Contraseña</button>
            </div>
        </div>
    </form>
</section>

<?php include '../includes/footer.php'; ?>

<script>
    (function () {
        'use strict';
        window.addEventListener('load', function () {
            var forms = document.getElementsByClassName('needs-validation');
            var validation = Array.prototype.filter.call(forms, function (form) {
                form.addEventListener('submit', function (event) {
                    if (form.checkValidity() === false) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        }, false);
    })();
</script>
