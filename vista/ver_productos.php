<?php
require_once '../config/conexion.php';

$database = new Database();
$conn = $database->getConnection();

// Procesar eliminación del producto si se ha enviado el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'borrarProducto') {
    $idProducto = $_POST['idProducto'];

    // Iniciar una transacción
    $conn->beginTransaction();

    try {
        // Verificar si el producto ha sido vendido
        $stmt = $conn->prepare("SELECT estado FROM transacciones WHERE idProducto = ? AND estado = 'vendido'");
        $stmt->execute([$idProducto]);

        if ($stmt->rowCount() > 0) {
            // Si el producto ha sido vendido, no eliminar el producto
            throw new Exception("No se puede eliminar el producto porque ha sido vendido.");
        } else {
            // Eliminar dependencias del producto en la tabla 'megusta'
            $stmt = $conn->prepare("DELETE FROM megusta WHERE idProducto = ?");
            $stmt->execute([$idProducto]);

            // Eliminar fotos asociadas al producto
            $stmt = $conn->prepare("DELETE FROM fotos WHERE idProducto = ?");
            $stmt->execute([$idProducto]);

            // Eliminar transacciones asociadas al producto
            $stmt = $conn->prepare("DELETE FROM transacciones WHERE idProducto = ?");
            $stmt->execute([$idProducto]);

            // Eliminar el producto
            $stmt = $conn->prepare("DELETE FROM productos WHERE idProducto = ?");
            $stmt->execute([$idProducto]);

            // Confirmar la transacción
            $conn->commit();
            $success_message = "Producto y todas sus dependencias han sido eliminadas.";
        }
    } catch (Exception $e) {
        // Revertir la transacción en caso de error
        $conn->rollBack();
        $error_message = "Error al eliminar el producto: " . $e->getMessage();
    }
}

// Obtener término de búsqueda
$busqueda = isset($_GET['busqueda']) ? $_GET['busqueda'] : '';

// Obtener productos, sus fotos, estado de transacción y usuario correspondiente
$query = "SELECT p.*, u.nombreUsuario AS nombreVendedor, c.nombreCategoria, f.nombreFoto, t.estado, 
                 (SELECT nombreUsuario FROM usuarios WHERE idUsuario = t.idComprador) AS nombreComprador
          FROM productos p
          JOIN usuarios u ON p.idUsuario = u.idUsuario
          JOIN categorias c ON p.idCategoria = c.idCategoria
          LEFT JOIN fotos f ON p.idProducto = f.idProducto
          LEFT JOIN transacciones t ON p.idProducto = t.idProducto
          WHERE p.nombreProducto LIKE :busqueda
             OR p.descripcion LIKE :busqueda
             OR c.nombreCategoria LIKE :busqueda
             OR u.nombreUsuario LIKE :busqueda";
$stmt = $conn->prepare($query);
$paramBusqueda = '%' . $busqueda . '%';
$stmt->bindParam(':busqueda', $paramBusqueda);
$stmt->execute();
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

    <!-- Contenido de la página -->
    <main id="page-content-wrapper" class="flex-grow-1 d-flex flex-column" style="min-width: 0;">
        <div class="container mt-5">
            <h2>Gestión de Productos</h2>

            <!-- Formulario de búsqueda -->
            <form class="d-flex mb-3" action="ver_productos.php" method="GET">
                <input class="form-control me-2 text-dark border-danger" type="search" name="busqueda" placeholder="Buscar productos" aria-label="Buscar" value="<?php echo htmlspecialchars($busqueda); ?>">
                <button type="submit" class="btn btn-light text-danger"><i class="fas fa-search"></i></button>
            </form>

            <?php if (isset($success_message)): ?>
                <div class="alert alert-success">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nombre del Producto</th>
                            <th>Talla</th>
                            <th>Descripción</th>
                            <th>Precio</th>
                            <th>Condición</th>
                            <th>Estado</th>
                            <th>Vendedor</th>
                            <th>Comprador</th>
                            <th>Categoría</th>
                            <th>Fotos</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        // Agrupar productos por ID de producto
                        $productosAgrupados = [];
                        foreach ($productos as $producto) {
                            $idProducto = $producto['idProducto'];
                            if (!isset($productosAgrupados[$idProducto])) {
                                $productosAgrupados[$idProducto] = $producto;
                                $productosAgrupados[$idProducto]['fotos'] = [];
                            }
                            if ($producto['nombreFoto']) {
                                $productosAgrupados[$idProducto]['fotos'][] = $producto['nombreFoto'];
                            }
                        }
                        foreach ($productosAgrupados as $producto): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($producto['idProducto']); ?></td>
                                <td><?php echo htmlspecialchars($producto['nombreProducto']); ?></td>
                                <td><?php echo htmlspecialchars($producto['talla']); ?></td>
                                <td><?php echo htmlspecialchars($producto['descripcion']); ?></td>
                                <td>€<?php echo htmlspecialchars($producto['precio']); ?></td>
                                <td><?php echo htmlspecialchars($producto['condicion']); ?></td>
                                <td><?php echo htmlspecialchars($producto['estado']); ?></td>
                                <td><?php echo htmlspecialchars($producto['nombreVendedor']); ?></td>
                                <td><?php echo htmlspecialchars($producto['nombreComprador']); ?></td>
                                <td><?php echo htmlspecialchars($producto['nombreCategoria']); ?></td>
                                <td>
                                    <?php if (!empty($producto['fotos'])): ?>
                                        <?php foreach ($producto['fotos'] as $foto): ?>
                                            <img src="<?php echo htmlspecialchars($foto); ?>" alt="Foto de producto" class="img-thumbnail" style="width: 50px; height: 50px;">
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        No hay fotos
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="post" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este producto?');">
                                        <input type="hidden" name="idProducto" value="<?php echo $producto['idProducto']; ?>">
                                        <input type="hidden" name="action" value="borrarProducto">
                                        <button type="submit" class="btn btn-danger btn-sm">Borrar</button>
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

<?php include '../includes/footer_admin.php'; ?>
