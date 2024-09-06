<?php
require_once '../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];

    $database = new Database();
    $conn = $database->getConnection();

    // Verificar si el correo electrónico existe en la base de datos
    $query = "SELECT idUsuario, nombreUsuario FROM usuarios WHERE email = :email";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() == 1) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $idUsuario = $row['idUsuario'];
        $nombreUsuario = $row['nombreUsuario'];

        // Guardar el ID del usuario en la sesión
        session_start();
        $_SESSION['idUsuario'] = $idUsuario;
        $_SESSION['email'] = $email;

        // Redirigir al formulario para establecer una nueva contraseña
        header("Location: establecer_contrasena.php");
        exit();
    } else {
        $mensaje = "El correo electrónico no está registrado.";
        header("Location: recuperar.php?mensaje=" . urlencode($mensaje));
        exit();
    }
}
?>

<?php include '../includes/header.php'; ?>

<section class="recuperar container py-5">
    <h2 class="text-center mb-4">Recuperar Contraseña</h2>
    <?php if (isset($_GET['mensaje'])): ?>
        <div class="alert alert-danger text-center"><?php echo $_GET['mensaje']; ?></div>
    <?php endif; ?>
    <form id="recuperarForm" action="recuperar.php" method="post" class="needs-validation" novalidate>
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label for="email" class="form-label">Correo Electrónico</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                    <div class="invalid-feedback">Por favor, ingrese su correo electrónico.</div>
                </div>
                <button type="submit" class="btn btn-danger btn-block">Enviar</button>
            </div>
        </div>
    </form>
</section>

<?php include '../includes/footer.php'; ?>
