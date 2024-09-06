<?php
session_start();
require_once '../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $database = new Database();
    $db = $database->getConnection();

    $query = "SELECT usuarios.idUsuario, usuarios.nombreUsuario, usuarios.password, usuarios.fechaBaja, roles.nombreRol 
              FROM usuarios 
              JOIN usuarios_roles ON usuarios.idUsuario = usuarios_roles.idUsuario 
              JOIN roles ON usuarios_roles.idRol = roles.idRol 
              WHERE email = :email";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    if ($stmt->rowCount() == 1) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Verificar si el usuario está dado de baja
        if (!is_null($row['fechaBaja'])) {
            $error = "Tu cuenta ha sido dada de baja. Por favor, regístrate nuevamente.";
            header("Location: ../vista/login.php?error=" . urlencode($error));
            exit();
        }
        
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['idUsuario'];
            $_SESSION['username'] = $row['nombreUsuario'];
            $_SESSION['role'] = $row['nombreRol'];
            
            // Manejar "Recuérdame"
            if (isset($_POST['rememberMe'])) {
                setcookie('email', $email, time() + (86400 * 30), "/"); // 30 días
                setcookie('password', $password, time() + (86400 * 30), "/"); // 30 días
                setcookie('rememberMe', 'checked', time() + (86400 * 30), "/");
            } else {
                setcookie('email', '', time() - 3600, "/");
                setcookie('password', '', time() - 3600, "/");
                setcookie('rememberMe', '', time() - 3600, "/");
            }

            switch ($row['nombreRol']) {
                case 'admin':
                    header("Location: ../vista/admin.php");
                    break;
                case 'usuario':
                    header("Location: ../vista/principal.php");
                    break;
                default:
                    $error = "Tipo de usuario no reconocido.";
                    header("Location: ../vista/login.php?error=" . urlencode($error));
                    break;
            }
            exit();
        } else {
            $error = "Correo electrónico o contraseña incorrectos.";
            header("Location: ../vista/login.php?error=" . urlencode($error));
        }
    } else {
        $error = "Correo electrónico o contraseña incorrectos.";
        header("Location: ../vista/login.php?error=" . urlencode($error));
    }
}
?>

<?php include '../includes/header.php'; ?>

<section class="login container py-5">
    <h2 class="text-center mb-4">INICIAR SESIÓN</h2>
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger text-center"><?php echo $_GET['error']; ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['mensaje'])): ?>
        <div class="alert alert-success text-center"><?php echo $_GET['mensaje']; ?></div>
    <?php endif; ?>
    <form id="loginForm" action="login.php" method="post" class="needs-validation" novalidate>
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label for="email" class="form-label">Correo Electrónico</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($_COOKIE['email']) ? $_COOKIE['email'] : ''; ?>" required>
                    <div class="invalid-feedback">Por favor, ingrese su correo electrónico.</div>
                </div>
                <div class="form-group mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" class="form-control" id="password" name="password" value="<?php echo isset($_COOKIE['password']) ? $_COOKIE['password'] : ''; ?>" required>
                    <div class="invalid-feedback">Por favor, ingrese su contraseña.</div>
                </div>
                <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" id="rememberMe" name="rememberMe" <?php echo isset($_COOKIE['rememberMe']) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="rememberMe">Recuérdame</label>
                </div>
                <button type="submit" class="btn btn-danger btn-block">INICIAR SESIÓN</button>
                <p class="text-center mt-3">¿No tienes una cuenta? <a href="registro.php">Regístrate aquí</a></p>
                <p class="text-center mt-3"><a href="recuperar.php">¿Olvidaste tu contraseña?</a></p>
            </div>
        </div>
    </form>
</section>

<?php include '../includes/footer.php'; ?>
