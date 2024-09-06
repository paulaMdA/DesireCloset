<?php
require_once '../config/conexion.php';

$database = new Database();
$conn = $database->getConnection();

// Inicializar variables de mensaje
$success_message = '';
$error_message = '';

// Manejar la eliminación y edición del usuario y sus dependencias
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['idUsuario']) && $_POST['action'] == 'eliminarUsuario') {
        $idUsuario = $_POST['idUsuario'];

        try {
            // Iniciar una transacción
            $conn->beginTransaction();

            // Obtener todos los productos del usuario para eliminar sus dependencias
            $stmt = $conn->prepare("SELECT idProducto FROM productos WHERE idUsuario = ?");
            $stmt->execute([$idUsuario]);
            $productos = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // Eliminar dependencias de cada producto del usuario
            foreach ($productos as $idProducto) {
                $stmt = $conn->prepare("DELETE FROM megusta WHERE idProducto = ?");
                $stmt->execute([$idProducto]);

                $stmt = $conn->prepare("DELETE FROM fotos WHERE idProducto = ?");
                $stmt->execute([$idProducto]);

                $stmt = $conn->prepare("DELETE FROM transacciones WHERE idProducto = ?");
                $stmt->execute([$idProducto]);

                $stmt = $conn->prepare("DELETE FROM mensajes WHERE idProducto = ?");
                $stmt->execute([$idProducto]);
            }

            // Eliminar productos del usuario
            $stmt = $conn->prepare("DELETE FROM productos WHERE idUsuario = ?");
            $stmt->execute([$idUsuario]);

            // Eliminar dependencias del usuario en las tablas relacionadas
            $stmt = $conn->prepare("DELETE FROM fotos WHERE idUsuario = ?");
            $stmt->execute([$idUsuario]);

            $stmt = $conn->prepare("DELETE FROM transacciones WHERE idComprador = ? OR idVendedor = ?");
            $stmt->execute([$idUsuario, $idUsuario]);

            $stmt = $conn->prepare("DELETE FROM mensajes WHERE idEmisor = ? OR idReceptor = ?");
            $stmt->execute([$idUsuario, $idUsuario]);

            $stmt = $conn->prepare("DELETE FROM valoraciones WHERE idValorado = ? OR idValorador = ?");
            $stmt->execute([$idUsuario, $idUsuario]);

            $stmt = $conn->prepare("DELETE FROM validaciondni WHERE idUsuario = ?");
            $stmt->execute([$idUsuario]);

            $stmt = $conn->prepare("DELETE FROM megusta WHERE idUsuario = ?");
            $stmt->execute([$idUsuario]);

            $stmt = $conn->prepare("DELETE FROM usuarios_roles WHERE idUsuario = ?");
            $stmt->execute([$idUsuario]);

            // Finalmente, eliminar el usuario
            $stmt = $conn->prepare("DELETE FROM usuarios WHERE idUsuario = ?");
            $stmt->execute([$idUsuario]);

            $conn->commit();
            $success_message = "Usuario y todas sus dependencias han sido eliminadas.";
        } catch (Exception $e) {
            $conn->rollBack();
            $error_message = "Error al eliminar el usuario: " . $e->getMessage();
        }
    } elseif (isset($_POST['idUsuario']) && $_POST['action'] == 'editarUsuario') {
        $idUsuario = $_POST['idUsuario'];
        $nombreUsuario = $_POST['nombreUsuario'];
        $nombre = $_POST['nombre'];
        $apellidos1 = $_POST['apellidos1'];
        $apellidos2 = $_POST['apellidos2'];
        $email = $_POST['email'];
        $sexo = $_POST['sexo'];
        $descripcion = $_POST['descripcion'];
        $fechaNacimiento = $_POST['fechaNacimiento'];
        $fechaRegistro = $_POST['fechaRegistro'];
        $fechaBaja = $_POST['fechaBaja'];
        $foto = $_POST['foto'];
        $pagado = isset($_POST['pagado']) ? 1 : 0;

        try {
            // Obtener los valores actuales de fechaRegistro y fechaBaja
            $stmt = $conn->prepare("SELECT fechaRegistro, fechaBaja FROM usuarios WHERE idUsuario = ?");
            $stmt->execute([$idUsuario]);
            $usuarioActual = $stmt->fetch(PDO::FETCH_ASSOC);

            if (empty($fechaRegistro)) {
                $fechaRegistro = $usuarioActual['fechaRegistro'];
            }

            if (empty($fechaBaja)) {
                $fechaBaja = $usuarioActual['fechaBaja'];
            }

            $stmt = $conn->prepare("UPDATE usuarios SET nombreUsuario = ?, nombre = ?, apellidos1 = ?, apellidos2 = ?, email = ?, sexo = ?, descripcion = ?, fechaNacimiento = ?, fechaRegistro = ?, fechaBaja = ?, foto = ?, pagado = ? WHERE idUsuario = ?");
            $stmt->execute([$nombreUsuario, $nombre, $apellidos1, $apellidos2, $email, $sexo, $descripcion, $fechaNacimiento, $fechaRegistro, $fechaBaja, $foto, $pagado, $idUsuario]);
            $success_message = "Usuario editado con éxito.";
        } catch (Exception $e) {
            $error_message = "Error al editar el usuario: " . $e->getMessage();
        }
    }
}

// Obtener término de búsqueda
$busqueda = isset($_GET['busqueda']) ? $_GET['busqueda'] : '';

// Obtener usuarios
$query = "SELECT u.idUsuario, u.nombreUsuario, u.nombre, u.apellidos1, u.apellidos2, u.email, u.sexo, u.descripcion, u.fechaNacimiento, u.fechaRegistro, u.fechaBaja, u.foto, u.pagado, r.nombreRol 
          FROM usuarios u 
          JOIN usuarios_roles ur ON u.idUsuario = ur.idUsuario
          JOIN roles r ON ur.idRol = r.idRol
          WHERE r.nombreRol = 'usuario' 
          AND (u.nombreUsuario LIKE :busqueda 
               OR u.nombre LIKE :busqueda 
               OR u.apellidos1 LIKE :busqueda 
               OR u.apellidos2 LIKE :busqueda 
               OR u.email LIKE :busqueda)";
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
            <h2>Gestión de Usuarios</h2>

            <!-- Formulario de búsqueda -->
            <form class="d-flex mb-3" action="ver_usuarios.php" method="GET">
                <input class="form-control me-2 text-dark border-danger" type="search" name="busqueda" placeholder="Buscar usuarios" aria-label="Buscar" value="<?php echo htmlspecialchars($busqueda); ?>">
                <button type="submit" class="btn btn-light text-danger"><i class="fas fa-search"></i></button>
            </form>

            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            <?php if ($error_message): ?>
                <div class="alert alert-danger">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nombre de Usuario</th>
                            <th>Nombre</th>
                            <th>Apellidos</th>
                            <th>Email</th>
                            <th>Sexo</th>
                            <th>Descripción</th>
                            <th>Fecha de Nacimiento</th>
                            <th>Fecha de Registro</th>
                            <th>Fecha de Baja</th>
                            <th>Foto</th>
                            <th>Pagado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($usuario['idUsuario']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['nombreUsuario']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['apellidos1'] . ' ' . $usuario['apellidos2']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['sexo']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['descripcion']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['fechaNacimiento']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['fechaRegistro']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['fechaBaja']); ?></td>
                                <td><img src="<?php echo htmlspecialchars($usuario['foto']); ?>" alt="Foto de perfil" style="width: 50px; height: 50px;"></td>
                                <td><?php echo $usuario['pagado'] ? 'Sí' : 'No'; ?></td>
                                <td>
                                    <button class="btn btn-primary btn-sm" onclick="editarUsuario(
                                        '<?php echo $usuario['idUsuario']; ?>', 
                                        '<?php echo $usuario['nombreUsuario']; ?>', 
                                        '<?php echo $usuario['nombre']; ?>',
                                        '<?php echo $usuario['apellidos1']; ?>',
                                        '<?php echo $usuario['apellidos2']; ?>',
                                        '<?php echo $usuario['email']; ?>', 
                                        '<?php echo $usuario['sexo']; ?>', 
                                        '<?php echo $usuario['descripcion']; ?>', 
                                        '<?php echo $usuario['fechaNacimiento']; ?>', 
                                        '<?php echo $usuario['fechaRegistro']; ?>', 
                                        '<?php echo $usuario['fechaBaja']; ?>', 
                                        '<?php echo $usuario['foto']; ?>', 
                                        '<?php echo $usuario['pagado']; ?>')">Editar</button>
                                    <form method="post" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este usuario y todas sus dependencias?');" style="display:inline;">
                                        <input type="hidden" name="idUsuario" value="<?php echo $usuario['idUsuario']; ?>">
                                        <input type="hidden" name="action" value="eliminarUsuario">
                                        <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                    </form>
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

<!-- Modal para editar usuario -->
<div class="modal fade" id="editarUsuarioModal" tabindex="-1" aria-labelledby="editarUsuarioModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header">
                    <h5 class="modal-title" id="editarUsuarioModalLabel">Editar Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="edit_user_id" name="idUsuario">
                    <div class="mb-3">
                        <label for="nombreUsuario" class="form-label">Nombre de Usuario</label>
                        <input type="text" class="form-control" id="nombreUsuario" name="nombreUsuario">
                    </div>
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="nombre" name="nombre">
                    </div>
                    <div class="mb-3">
                        <label for="apellidos1" class="form-label">Primer Apellido</label>
                        <input type="text" class="form-control" id="apellidos1" name="apellidos1">
                    </div>
                    <div class="mb-3">
                        <label for="apellidos2" class="form-label">Segundo Apellido</label>
                        <input type="text" class="form-control" id="apellidos2" name="apellidos2">
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email">
                    </div>
                    <div class="mb-3">
                        <label for="sexo" class="form-label">Sexo</label>
                        <input type="text" class="form-control" id="sexo" name="sexo">
                    </div>
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="fechaNacimiento" class="form-label">Fecha de Nacimiento</label>
                        <input type="date" class="form-control" id="fechaNacimiento" name="fechaNacimiento">
                    </div>
                    <div class="mb-3">
                        <label for="fechaRegistro" class="form-label">Fecha de Registro</label>
                        <input type="date" class="form-control" id="fechaRegistro" name="fechaRegistro" max="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="fechaBaja" class="form-label">Fecha de Baja</label>
                        <input type="date" class="form-control" id="fechaBaja" name="fechaBaja" max="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="foto" class="form-label">Foto</label>
                        <input type="text" class="form-control" id="foto" name="foto">
                    </div>
                    <div class="mb-3">
                        <label for="pagado" class="form-label">Pagado</label>
                        <input type="checkbox" class="form-check-input" id="pagado" name="pagado" value="1">
                    </div>
                    <input type="hidden" name="action" value="editarUsuario">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-dark" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer_admin.php'; ?>

<script>
    function editarUsuario(id, nombreUsuario, nombre, apellidos1, apellidos2, email, sexo, descripcion, fechaNacimiento, fechaRegistro, fechaBaja, foto, pagado) {
        document.getElementById('edit_user_id').value = id;
        document.getElementById('nombreUsuario').value = nombreUsuario;
        document.getElementById('nombre').value = nombre;
        document.getElementById('apellidos1').value = apellidos1;
        document.getElementById('apellidos2').value = apellidos2;
        document.getElementById('email').value = email;
        document.getElementById('sexo').value = sexo;
        document.getElementById('descripcion').value = descripcion;
        document.getElementById('fechaNacimiento').value = fechaNacimiento;
        document.getElementById('fechaRegistro').value = fechaRegistro;
        document.getElementById('fechaBaja').value = fechaBaja;
        document.getElementById('foto').value = foto;
        document.getElementById('pagado').checked = pagado == 1 ? true : false;
        var myModal = new bootstrap.Modal(document.getElementById('editarUsuarioModal'));
        myModal.show();
    }
</script>
