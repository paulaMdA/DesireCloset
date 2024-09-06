<?php
require_once '../config/conexion.php';

$success_message = '';
$error_message = '';

// Procesar formulario si se ha enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idUsuario = $_POST['idUsuario'];
    $action = $_POST['action'];

    $database = new Database();
    $conn = $database->getConnection();

    if ($action == 'validar') {
        $estado = 'validado';
        $success_message = "La verificación del DNI ha sido validada exitosamente.";
    } else if ($action == 'rechazar') {
        $estado = 'rechazado';
        $success_message = "La verificación del DNI ha sido rechazada.";
        
        // Eliminar el usuario y sus dependencias
        try {
            $conn->beginTransaction();

            // Eliminar fotos asociadas al usuario
            $stmt = $conn->prepare("DELETE FROM fotos WHERE idUsuario = ?");
            $stmt->execute([$idUsuario]);

            // Eliminar transacciones asociadas al usuario
            $stmt = $conn->prepare("DELETE FROM transacciones WHERE idComprador = ? OR idVendedor = ?");
            $stmt->execute([$idUsuario, $idUsuario]);

            // Eliminar productos asociados al usuario
            $stmt = $conn->prepare("DELETE FROM productos WHERE idUsuario = ?");
            $stmt->execute([$idUsuario]);

            // Eliminar me gusta asociados al usuario
            $stmt = $conn->prepare("DELETE FROM megusta WHERE idUsuario = ?");
            $stmt->execute([$idUsuario]);

            // Eliminar mensajes asociados al usuario
            $stmt = $conn->prepare("DELETE FROM mensajes WHERE idEmisor = ? OR idReceptor = ?");
            $stmt->execute([$idUsuario, $idUsuario]);

            // Eliminar roles asociados al usuario
            $stmt = $conn->prepare("DELETE FROM usuarios_roles WHERE idUsuario = ?");
            $stmt->execute([$idUsuario]);

            // Eliminar validaciones de DNI asociadas al usuario
            $stmt = $conn->prepare("DELETE FROM validaciondni WHERE idUsuario = ?");
            $stmt->execute([$idUsuario]);

            // Eliminar valoraciones asociadas al usuario
            $stmt = $conn->prepare("DELETE FROM valoraciones WHERE idValorado = ? OR idValorador = ?");
            $stmt->execute([$idUsuario, $idUsuario]);

            // Eliminar el usuario
            $stmt = $conn->prepare("DELETE FROM usuarios WHERE idUsuario = ?");
            $stmt->execute([$idUsuario]);

            $conn->commit();
        } catch (Exception $e) {
            $conn->rollBack();
            $error_message = "Error al eliminar el usuario: " . $e->getMessage();
        }
    } else {
        $error_message = "Acción no válida.";
    }

    if (empty($error_message)) {
        try {
            $stmt = $conn->prepare("UPDATE validaciondni SET estado = ?, fechaValidacion = NOW() WHERE idUsuario = ?");
            $stmt->execute([$estado, $idUsuario]);
        } catch (Exception $e) {
            $error_message = "Error al actualizar el estado: " . $e->getMessage();
        }
    }
}

$database = new Database();
$conn = $database->getConnection();

// Obtener término de búsqueda
$busqueda = isset($_GET['busqueda']) ? $_GET['busqueda'] : '';

// Obtener usuarios con rol 2 (usuario) y sus estados de validación
$query = "SELECT u.idUsuario, u.nombreUsuario, u.email, u.fechaNacimiento, u.foto, v.dni AS fotoDNI, v.estado 
          FROM usuarios u
          JOIN usuarios_roles ur ON u.idUsuario = ur.idUsuario
          JOIN validaciondni v ON u.idUsuario = v.idUsuario
          WHERE ur.idRol = 2 AND (u.nombreUsuario LIKE :busqueda
             OR u.nombre LIKE :busqueda
             OR u.apellidos1 LIKE :busqueda
             OR u.apellidos2 LIKE :busqueda)";
$stmt = $conn->prepare($query);
$paramBusqueda = '%' . $busqueda . '%';
$stmt->bindParam(':busqueda', $paramBusqueda);
$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header_admin.php';
?>

<div class="admin d-flex" id="wrapper" style="min-height: 100vh; overflow-x: hidden;">
    <!-- Sidebar -->
    <div class="bg-dark border-right" id="sidebar-wrapper" style="width: 150px;">
        <div class="sidebar-heading text-white">DesireCloset Admin</div>
        <div class="list-group list-group-flush">
            <a href="admin.php" class="list-group-item list-group-item-action bg-dark text-white">Dashboard</a>
            <a href="ver_usuarios.php" class="list-group-item list-group-item-action bg-dark text-white">Usuarios</a>
            <a href="ver_productos.php" class="list-group-item list-group-item-action bg-dark text-white">Productos</a>
            <a href="estadistica_productos.php" class="list-group-item list-group-item-action bg-dark text-white">Estadística</a>
            <a href="verificar_dni.php" class="list-group-item list-group-item-action bg-dark text-white">Verificar DNI</a>
            <a href="logout.php" class="list-group-item list-group-item-action bg-dark text-white">Cerrar Sesión</a>
        </div>
    </div>
    <!-- /#sidebar-wrapper -->

    <!-- Page Content -->
    <main id="page-content-wrapper" class="flex-grow-1 d-flex flex-column" style="min-width: 0;">
        <div class="container mt-5">
            <h2>Verificación de DNI</h2>

            <!-- Formulario de búsqueda -->
            <form class="d-flex mb-3" action="verificar_dni.php" method="GET">
                <input class="form-control me-2 text-dark border-danger" type="search" name="busqueda" placeholder="Buscar usuarios" aria-label="Buscar" value="<?php echo htmlspecialchars($busqueda); ?>">
                <button type="submit" class="btn btn-light text-danger"><i class="fas fa-search"></i></button>
            </form>

            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php elseif (!empty($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>ID Usuario</th>
                            <th>Nombre de Usuario</th>
                            <th>Fecha de Nacimiento</th>
                            <th>Foto</th>
                            <th>Foto DNI</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($usuario['idUsuario']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['nombreUsuario']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['fechaNacimiento']); ?></td>
                                <td><img src="<?php echo htmlspecialchars($usuario['foto']); ?>" alt="Foto de perfil" style="width: 50px; height: 50px;"></td>
                                <td><img src="<?php echo htmlspecialchars($usuario['fotoDNI']); ?>" alt="Foto del DNI" style="width: 100px; height: 50px;"></td>
                                <td style="color: <?php echo $usuario['estado'] == 'pendiente' ? 'red' : 'black'; ?>;">
                                    <?php echo htmlspecialchars($usuario['estado']); ?>
                                </td>
                                <td>
                                    <?php if ($usuario['estado'] == 'pendiente'): ?>
                                        <form method="post" action="verificar_dni.php">
                                            <input type="hidden" name="idUsuario" value="<?php echo $usuario['idUsuario']; ?>">
                                            <button type="submit" name="action" value="validar" class="btn btn-success btn-sm">Validar</button>
                                            <button type="submit" name="action" value="rechazar" class="btn btn-danger btn-sm">Rechazar</button>
                                        </form>
                                    <?php else: ?>
                                        No hay acciones disponibles
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
    <!-- /#page-content-wrapper -->
</div>
<!-- /#wrapper -->

<?php include '../includes/footer_admin.php'; ?>
