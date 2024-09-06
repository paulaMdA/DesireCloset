<?php
session_start();
require_once '../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['cancel'])) {
        // Limpiar la sesión y redirigir a la página principal
        session_unset();
        session_destroy();
        header('Location: ../index.php');
        exit();
    } else {
        // Simular un proceso de pago exitoso
        $pagoExitoso = true;

        if ($pagoExitoso) {
            try {
                $database = new Database();
                $conn = $database->getConnection();

                // Iniciar una transacción
                $conn->beginTransaction();

                // Verificar si el correo electrónico ya existe en la base de datos
                $query = "SELECT idUsuario, fechaBaja, pagado FROM usuarios WHERE email = :email";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':email', $_SESSION['email']);
                $stmt->execute();

                if ($stmt->rowCount() == 1) {
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    $idUsuario = $row['idUsuario'];
                    $fechaBaja = $row['fechaBaja'];
                    $pagado = $row['pagado'];

                    if (!is_null($fechaBaja) && !$pagado) {
                        // El correo electrónico ya existe y el usuario está dado de baja, actualizar los datos del usuario
                        $query = "UPDATE usuarios 
                                  SET nombreUsuario = :nombreUsuario, password = :password, nombre = :nombre, apellidos1 = :apellidos1, apellidos2 = :apellidos2, sexo = :sexo, descripcion = :descripcion, fechaNacimiento = :fechaNacimiento, foto = :foto, pagado = 1, fechaBaja = NULL 
                                  WHERE idUsuario = :idUsuario";
                        $stmt = $conn->prepare($query);
                        $stmt->bindParam(':nombreUsuario', $_SESSION['nombreUsuario']);
                        $stmt->bindParam(':password', $_SESSION['password']);
                        $stmt->bindParam(':nombre', $_SESSION['nombre']);
                        $stmt->bindParam(':apellidos1', $_SESSION['apellidos1']);
                        $stmt->bindParam(':apellidos2', $_SESSION['apellidos2']);
                        $stmt->bindParam(':sexo', $_SESSION['sexo']);
                        $stmt->bindParam(':descripcion', $_SESSION['descripcion']);
                        $stmt->bindParam(':fechaNacimiento', $_SESSION['fechaNacimiento']);
                        $stmt->bindParam(':foto', $_SESSION['rutaFotoPerfil']);
                        $stmt->bindParam(':idUsuario', $idUsuario);
                        $stmt->execute();

                        // Actualizar validación de DNI
                        if ($_SESSION['rutaFotoDNI']) {
                            $query = "UPDATE validaciondni SET dni = :dni, estado = 'pendiente', fechaValidacion = NOW() WHERE idUsuario = :idUsuario";
                            $stmt = $conn->prepare($query);
                            $stmt->bindParam(':dni', $_SESSION['rutaFotoDNI']);
                            $stmt->bindParam(':idUsuario', $idUsuario);
                            $stmt->execute();
                        }

                    } else {
                        throw new Exception("El correo electrónico ya está registrado. Por favor, utiliza otro correo electrónico.");
                    }

                } else {
                    // El correo electrónico no existe, insertar nuevo usuario
                    $query = "INSERT INTO usuarios (nombreUsuario, password, email, nombre, apellidos1, apellidos2, sexo, descripcion, fechaNacimiento, foto, pagado) 
                              VALUES (:nombreUsuario, :password, :email, :nombre, :apellidos1, :apellidos2, :sexo, :descripcion, :fechaNacimiento, :foto, 1)";
                    $stmt = $conn->prepare($query);
                    $stmt->bindParam(':nombreUsuario', $_SESSION['nombreUsuario']);
                    $stmt->bindParam(':password', $_SESSION['password']);
                    $stmt->bindParam(':email', $_SESSION['email']);
                    $stmt->bindParam(':nombre', $_SESSION['nombre']);
                    $stmt->bindParam(':apellidos1', $_SESSION['apellidos1']);
                    $stmt->bindParam(':apellidos2', $_SESSION['apellidos2']);
                    $stmt->bindParam(':sexo', $_SESSION['sexo']);
                    $stmt->bindParam(':descripcion', $_SESSION['descripcion']);
                    $stmt->bindParam(':fechaNacimiento', $_SESSION['fechaNacimiento']);
                    $stmt->bindParam(':foto', $_SESSION['rutaFotoPerfil']);
                    $stmt->execute();

                    $idUsuario = $conn->lastInsertId();

                    // Insertar rol del usuario
                    $query = "INSERT INTO usuarios_roles (idUsuario, idRol) VALUES (:idUsuario, 2)";
                    $stmt = $conn->prepare($query);
                    $stmt->bindParam(':idUsuario', $idUsuario);
                    $stmt->execute();

                    // Insertar validación de DNI
                    if ($_SESSION['rutaFotoDNI']) {
                        $query = "INSERT INTO validaciondni (dni, estado, idUsuario, fechaValidacion) VALUES (:dni, 'pendiente', :idUsuario, NOW())";
                        $stmt = $conn->prepare($query);
                        $stmt->bindParam(':dni', $_SESSION['rutaFotoDNI']);
                        $stmt->bindParam(':idUsuario', $idUsuario);
                        $stmt->execute();
                    }
                }

                // Confirmar la transacción
                $conn->commit();

                // Limpiar la sesión
                session_unset();
                session_destroy();

                // Redirigir a la página de inicio de sesión
                header('Location: login.php?mensaje=Registro y pago completados con éxito.');
                exit();
            } catch (PDOException $e) {
                if ($conn->inTransaction()) {
                    $conn->rollBack();
                }
                $error = "Error en el registro: " . $e->getMessage();
                header("Location: pago.php?error=" . urlencode($error));
                exit();
            }
        } else {
            header("Location: pago.php?error=El pago no se ha realizado correctamente.");
            exit();
        }
    }
}
?>

<?php include '../includes/header.php'; ?>

<div class="container mt-5">
    <h2 class="text-center mb-4">Pagar Suscripción</h2>
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger text-center"><?php echo $_GET['error']; ?></div>
    <?php endif; ?>
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header text-center">
                    Información Personal
                </div>
                <div class="card-body">
                    <form method="post" action="pago.php">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($_SESSION['nombre']); ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="apellidos" class="form-label">Apellidos</label>
                            <input type="text" class="form-control" id="apellidos" name="apellidos" value="<?php echo htmlspecialchars($_SESSION['apellidos1'] . ' ' . $_SESSION['apellidos2']); ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Correo Electrónico</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($_SESSION['email']); ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="amount" class="form-label">Total a pagar</label>
                            <input type="text" class="form-control" id="amount" name="amount" value="106€" readonly>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header text-center d-flex justify-content-between align-items-center">
                    Información de Pago
                    <button type="button" class="btn-close" aria-label="Close" onclick="window.location.href='../index.php';"></button>
                </div>
                <div class="card-body">
                    <form id="paymentForm" method="post" action="pago.php">
                        <div class="mb-3">
                            <label for="cardNumber" class="form-label">Número de Tarjeta</label>
                            <input type="text" class="form-control" id="cardNumber" name="cardNumber" required maxlength="19" placeholder="XXXX XXXX XXXX XXXX">
                            <div class="invalid-feedback">Por favor, ingrese un número de tarjeta válido (16 dígitos).</div>
                        </div>
                        <div class="mb-3">
                            <label for="cardName" class="form-label">Nombre en la Tarjeta</label>
                            <input type="text" class="form-control" id="cardName" name="cardName" required placeholder="Nombre Completo">
                            <div class="invalid-feedback">Por favor, ingrese el nombre tal como aparece en la tarjeta.</div>
                        </div>
                        <div class="mb-3">
                            <label for="expiryDate" class="form-label">Fecha de Expiración</label>
                            <input type="text" class="form-control" id="expiryDate" name="expiryDate" placeholder="MM/YY" required>
                            <div class="invalid-feedback">Por favor, ingrese una fecha de expiración válida (MM/YY).</div>
                        </div>
                        <div class="mb-3">
                            <label for="cvv" class="form-label">CVV</label>
                            <input type="text" class="form-control" id="cvv" name="cvv" required maxlength="4" placeholder="CVV">
                            <div class="invalid-feedback">Por favor, ingrese un CVV válido (3 o 4 dígitos).</div>
                        </div>
                        <button type="submit" class="btn btn-danger btn-block" style="width: 100%;">Pagar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
document.getElementById('paymentForm').addEventListener('submit', function(event) {
    event.preventDefault();
    var form = this;

    // Validación de los campos del formulario
    var cardNumber = document.getElementById('cardNumber').value;
    var cardName = document.getElementById('cardName').value;
    var expiryDate = document.getElementById('expiryDate').value;
    var cvv = document.getElementById('cvv').value;

    var cardNumberRegex = /^\d{16}$/;
    var expiryDateRegex = /^(0[1-9]|1[0-2])\/\d{2}$/;
    var cvvRegex = /^\d{3,4}$/;

    if (!cardNumberRegex.test(cardNumber.replace(/\s+/g, ''))) {
        Swal.fire('Error', 'Número de tarjeta inválido', 'error');
        return;
    }

    if (!expiryDateRegex.test(expiryDate)) {
        Swal.fire('Error', 'Fecha de expiración inválida', 'error');
        return;
    }

    if (!cvvRegex.test(cvv)) {
        Swal.fire('Error', 'CVV inválido', 'error');
        return;
    }

    Swal.fire({
        title: 'Confirmar pago',
        text: "Confirmar pago de 106€?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, pagar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire(
                'Pago realizado!',
                'Tu pago ha sido realizado con éxito.',
                'success'
            ).then(() => {
                form.submit(); // Enviar el formulario al backend para completar el registro
            });
        } else {
            Swal.fire(
                'Pago no realizado',
                'El pago no se ha realizado. El registro no se completará.',
                'error'
            ).then(() => {
                window.location.href = 'registro.php'; // Redirigir a la página de registro
            });
        }
    });
});
</script>
