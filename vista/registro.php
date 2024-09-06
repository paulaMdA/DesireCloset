<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombreUsuario = $_POST['nombreUsuario'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $email = $_POST['email'];
    $nombre = $_POST['nombre'];
    $apellidos1 = $_POST['apellidos1'];
    $apellidos2 = $_POST['apellidos2'];
    $sexo = $_POST['sexo'];
    $fechaNacimiento = $_POST['fechaNacimiento'];
    $descripcion = $_POST['descripcion'];
    $foto = $_FILES['foto']['name'];
    $dni = $_FILES['dni']['name'];

    // Subir foto de perfil
    if (!empty($foto)) {
        $fotoTempPerfil = $_FILES['foto']['tmp_name'];
        $hashedFotoPerfil = md5_file($fotoTempPerfil) . "_" . basename($foto);
        $rutaFotoPerfil = "../assets/uploads/$hashedFotoPerfil";
        if (!move_uploaded_file($fotoTempPerfil, $rutaFotoPerfil)) {
            $error = "Error al subir la foto de perfil.";
            header("Location: ../vista/registro.php?error=" . urlencode($error));
            exit();
        }
    } else {
        $rutaFotoPerfil = null;
    }

    // Subir foto del DNI
    if (!empty($dni)) {
        $fotoTempDNI = $_FILES['dni']['tmp_name'];
        $hashedFotoDNI = md5_file($fotoTempDNI) . "_" . basename($dni);
        $rutaFotoDNI = "../assets/uploads/$hashedFotoDNI";
        if (!move_uploaded_file($fotoTempDNI, $rutaFotoDNI)) {
            $error = "Error al subir la foto del DNI.";
            header("Location: ../vista/registro.php?error=" . urlencode($error));
            exit();
        }
    } else {
        $rutaFotoDNI = null;
    }

    // Almacenar datos en la sesión
    $_SESSION['nombreUsuario'] = $nombreUsuario;
    $_SESSION['password'] = $password;
    $_SESSION['email'] = $email;
    $_SESSION['nombre'] = $nombre;
    $_SESSION['apellidos1'] = $apellidos1;
    $_SESSION['apellidos2'] = $apellidos2;
    $_SESSION['sexo'] = $sexo;
    $_SESSION['fechaNacimiento'] = $fechaNacimiento;
    $_SESSION['descripcion'] = $descripcion;
    $_SESSION['rutaFotoPerfil'] = $rutaFotoPerfil;
    $_SESSION['rutaFotoDNI'] = $rutaFotoDNI;

    // Redirigir a la página de pago
    header('Location: pago.php');
    exit();
}
?>

<?php include '../includes/header.php'; ?>

<section class="registro container py-5">
    <h2 class="text-center mb-4">REGÍSTRATE COMO NUEVO USUARIO</h2>
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger text-center"><?php echo $_GET['error']; ?></div>
    <?php endif; ?>
    <form id="registrationForm" action="registro.php" method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
        <div class="row">
            <div class="col-md-6">
                <h4 class="mb-3">DATOS DE CUENTA</h4>
                <div class="form-group mb-3">
                    <label for="nombreUsuario" class="form-label">Nombre de Usuario</label>
                    <input type="text" class="form-control" id="nombreUsuario" name="nombreUsuario" required>
                    <div class="invalid-feedback">Por favor, ingrese su nombre de usuario.</div>
                </div>
                <div class="form-group mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                    <div class="invalid-feedback">Por favor, ingrese una contraseña.</div>
                </div>
                <div class="form-group mb-3">
                    <label for="confirm_password" class="form-label">Repetir Contraseña</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    <div class="invalid-feedback">Por favor, confirme su contraseña.</div>
                </div>
                <div class="form-group mb-3">
                    <label for="email" class="form-label">Correo Electrónico</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                    <div class="invalid-feedback">Por favor, ingrese un correo electrónico válido.</div>
                </div>
                <div class="form-group mb-3">
                    <label for="nombre" class="form-label">Nombre</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" required>
                    <div class="invalid-feedback">Por favor, ingrese su nombre.</div>
                </div>
                <div class="form-group mb-3">
                    <label for="apellidos1" class="form-label">Primer Apellido</label>
                    <input type="text" class="form-control" id="apellidos1" name="apellidos1" required>
                    <div class="invalid-feedback">Por favor, ingrese su primer apellido.</div>
                </div>
                <div class="form-group mb-3">
                    <label for="apellidos2" class="form-label">Segundo Apellido</label>
                    <input type="text" class="form-control" id="apellidos2" name="apellidos2">
                </div>
            </div>
            <div class="col-md-6">
                <h4 class="mb-3">DATOS PERFIL</h4>
                <div class="form-group mb-3">
                    <label for="foto" class="form-label">Foto de Perfil</label>
                    <input type="file" class="form-control" id="foto" name="foto" onchange="previewImage(event)">
                    <img id="fotoPreview" src="#" alt="Previsualización de Foto de Perfil" class="img-fluid rounded-circle mt-2" style="display: none; width: 150px; height: 150px;">
                </div>
                <div class="form-group mb-3">
                    <label for="descripcion" class="form-label">Descripción</label>
                    <textarea class="form-control" id="descripcion" name="descripcion" placeholder="Escríbenos un texto sobre ti, gustos y aficiones, saldrá publicado en tu perfil"></textarea>
                </div>
                <div class="form-group mb-3">
                    <label for="fechaNacimiento" class="form-label">Fecha de Nacimiento</label>
                    <input type="date" class="form-control" id="fechaNacimiento" name="fechaNacimiento" required>
                    <div class="invalid-feedback">Por favor, ingrese su fecha de nacimiento.</div>
                </div>
                <div class="form-group mb-3">
                    <label for="sexo" class="form-label">Sexo</label>
                    <select class="form-control" id="sexo" name="sexo" required>
                        <option value="" disabled selected>Seleccione su sexo</option>
                        <option value="masculino">Masculino</option>
                        <option value="femenino">Femenino</option>
                        <option value="otro">Otro</option>
                    </select>
                    <div class="invalid-feedback">Por favor, seleccione su sexo.</div>
                </div>
                <div class="form-group mb-3">
                    <label for="dni" class="form-label">Foto de DNI</label>
                    <input type="file" class="form-control" id="dni" name="dni" required>
                    <div class="invalid-feedback">Por favor, suba una foto de su DNI.</div>
                </div>
                <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" id="terms" required>
                    <label class="form-check-label" for="terms">Acepto los <a href="../vista/condiciones.php">términos y condiciones legales</a></label>
                </div>
                <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" id="age" required>
                    <label class="form-check-label" for="age">Acepto que soy mayor de edad</label>
                </div>
                <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" id="aceptarCookies" name="aceptarCookies">
                    <label class="form-check-label text-danger" for="aceptarCookies">Acepto la política de cookies</label>
                </div>
                <button type="submit" class="btn btn-danger btn-block">REGISTRARSE</button>
            </div>
        </div>
    </form>
</section>

<?php include '../includes/footer.php'; ?>

<script>
function previewImage(event) {
    var reader = new FileReader();
    reader.onload = function() {
        var output = document.getElementById('fotoPreview');
        output.src = reader.result;
        output.style.display = 'block';
    };
    reader.readAsDataURL(event.target.files[0]);
}
</script>
