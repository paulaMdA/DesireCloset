<?php
session_start();
require_once '../config/conexion.php';

$busqueda = isset($_GET['busqueda']) ? $_GET['busqueda'] : '';

if (empty($busqueda)) {
    echo 'Por favor, ingrese un término de búsqueda.';
    exit();
}

$database = new Database();
$db = $database->getConnection();

$resultadosUsuarios = [];
$resultadosProductos = [];

// Buscar usuarios
$queryUsuarios = "SELECT idUsuario, nombreUsuario, nombre, apellidos1, apellidos2, email FROM usuarios WHERE nombreUsuario LIKE :busqueda OR nombre LIKE :busqueda OR apellidos1 LIKE :busqueda OR apellidos2 LIKE :busqueda OR email LIKE :busqueda";
$stmtUsuarios = $db->prepare($queryUsuarios);
$paramBusqueda = '%' . $busqueda . '%';
$stmtUsuarios->bindParam(':busqueda', $paramBusqueda);
$stmtUsuarios->execute();
$resultadosUsuarios = $stmtUsuarios->fetchAll(PDO::FETCH_ASSOC);

// Buscar productos
$queryProductos = "SELECT idProducto, nombreProducto, descripcion, precio FROM productos WHERE nombreProducto LIKE :busqueda OR descripcion LIKE :busqueda";
$stmtProductos = $db->prepare($queryProductos);
$stmtProductos->bindParam(':busqueda', $paramBusqueda);
$stmtProductos->execute();
$resultadosProductos = $stmtProductos->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>

<div class="container mt-5">
    <h2 class="text-center mb-4">Resultados de la búsqueda para "<?php echo htmlspecialchars($busqueda); ?>"</h2>
    
    <?php if (!empty($resultadosUsuarios)): ?>
        <h3 class="mb-3">Usuarios</h3>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre de Usuario</th>
                        <th>Nombre</th>
                        <th>Apellidos</th>
                        <th>Email</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($resultadosUsuarios as $usuario): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($usuario['idUsuario']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['nombreUsuario']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['apellidos1'] . ' ' . $usuario['apellidos2']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($resultadosProductos)): ?>
        <h3 class="mt-4 mb-3">Productos</h3>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre del Producto</th>
                        <th>Descripción</th>
                        <th>Precio</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($resultadosProductos as $producto): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($producto['idProducto']); ?></td>
                            <td><?php echo htmlspecialchars($producto['nombreProducto']); ?></td>
                            <td><?php echo htmlspecialchars($producto['descripcion']); ?></td>
                            <td>€<?php echo htmlspecialchars($producto['precio']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
    
    <?php if (empty($resultadosUsuarios) && empty($resultadosProductos)): ?>
        <p class="text-center">No se encontraron resultados para "<?php echo htmlspecialchars($busqueda); ?>"</p>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
