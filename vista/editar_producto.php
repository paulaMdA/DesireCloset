<?php
require_once '../config/conexion.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Conectar a la base de datos
$database = new Database();
$conn = $database->getConnection();

$success_message = '';
$error_message = '';

if (isset($_GET['id'])) {
    $idProducto = $_GET['id'];

    $query = "SELECT * FROM productos WHERE idProducto = :idProducto AND idUsuario = :idUsuario";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':idProducto', $idProducto);
    $stmt->bindParam(':idUsuario', $_SESSION['user_id']);
    $stmt->execute();
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$producto) {
        $_SESSION['error'] = "Producto no encontrado.";
        header("Location: perfil.php");
        exit();
    }

    // Obtener las fotos del producto
    $queryFotos = "SELECT nombreFoto FROM fotos WHERE idProducto = :idProducto";
    $stmtFotos = $conn->prepare($queryFotos);
    $stmtFotos->bindParam(':idProducto', $idProducto);
    $stmtFotos->execute();
    $fotos = $stmtFotos->fetchAll(PDO::FETCH_ASSOC);

    // Obtener las categorías
    $queryCategorias = "SELECT idCategoria, nombreCategoria FROM categorias";
    $stmtCategorias = $conn->prepare($queryCategorias);
    $stmtCategorias->execute();
    $categorias = $stmtCategorias->fetchAll(PDO::FETCH_ASSOC);
} else {
    header("Location: perfil.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idProducto = $_POST['idProducto'];
    $nombreProducto = $_POST['nombreProducto'];
    $talla = $_POST['talla'];
    $descripcion = $_POST['descripcion'];
    $precio = $_POST['precio'];
    $condicion = $_POST['condicion'];
    $idCategoria = $_POST['idCategoria'];
    $idUsuario = $_SESSION['user_id'];

    // Manejo de las fotos del producto
    $fotosValidas = array_filter($_FILES['fotos']['name']);
    if (!empty($fotosValidas)) {
        $fotos = $_FILES['fotos'];
        $target_dir = "../assets/uploads/";

        // Verificar el número de fotos subidas
        if (count($fotosValidas) > 3) {
            $error_message = "Puedes subir un máximo de 3 fotos.";
        } else {
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            // Eliminar las fotos anteriores
            $queryDeleteFotos = "DELETE FROM fotos WHERE idProducto = :idProducto";
            $stmtDeleteFotos = $conn->prepare($queryDeleteFotos);
            $stmtDeleteFotos->bindParam(':idProducto', $idProducto);
            $stmtDeleteFotos->execute();

            // Subir nuevas fotos
            foreach ($fotosValidas as $index => $foto_name) {
                $tmp_name = $fotos['tmp_name'][$index];
                $target_file = $target_dir . md5_file($tmp_name) . "_" . basename($foto_name);
                if (move_uploaded_file($tmp_name, $target_file)) {
                    $foto_query = "INSERT INTO fotos (nombreFoto, idProducto, idUsuario) VALUES (:nombreFoto, :idProducto, :idUsuario)";
                    $foto_stmt = $conn->prepare($foto_query);
                    $foto_stmt->bindParam(':nombreFoto', $target_file);
                    $foto_stmt->bindParam(':idProducto', $idProducto);
                    $foto_stmt->bindParam(':idUsuario', $idUsuario);
                    if (!$foto_stmt->execute()) {
                        $error_message .= "Error al insertar la foto en la base de datos: " . $foto_stmt->errorInfo()[2] . "<br>";
                    }
                } else {
                    $error_message .= "Error al subir la foto: " . htmlspecialchars($foto_name) . "<br>";
                }
            }
        }
    }

    if (empty($error_message)) {
        $query = "UPDATE productos SET nombreProducto = :nombreProducto, talla = :talla, descripcion = :descripcion, precio = :precio, condicion = :condicion, idCategoria = :idCategoria WHERE idProducto = :idProducto";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':nombreProducto', $nombreProducto);
        $stmt->bindParam(':talla', $talla);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':precio', $precio);
        $stmt->bindParam(':condicion', $condicion);
        $stmt->bindParam(':idCategoria', $idCategoria);
        $stmt->bindParam(':idProducto', $idProducto);

        if ($stmt->execute()) {
            $success_message = "Producto actualizado con éxito.";
            // Redireccionar después de la actualización
            header("Location: editar_producto.php?id=$idProducto");
            exit();
        } else {
            $error_message = "Error al actualizar el producto en la base de datos: " . $stmt->errorInfo()[2];
        }
    }
}
?>

<?php if ($_SERVER['REQUEST_METHOD'] == 'POST'): ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Función para mostrar un mensaje de éxito con SweetAlert
        function mostrarMensajeExito(mensaje) {
            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: mensaje
            });
        }

        // Función para mostrar un mensaje de error con SweetAlert
        function mostrarMensajeError(mensaje) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: mensaje
            });
        }
    </script>

    <?php if (!empty($success_message)): ?>
        <script>
            mostrarMensajeExito("<?php echo $success_message; ?>");
        </script>
    <?php endif; ?>
    <?php if (!empty($error_message)): ?>
        <script>
            mostrarMensajeError("<?php echo $error_message; ?>");
        </script>
    <?php endif; ?>
<?php endif; ?>

<?php include '../includes/header.php'; ?>
<div class="container mt-5">
    <?php if ($_SERVER['REQUEST_METHOD'] == 'POST'): ?>
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success" role="alert">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
    <div class="registro_producto card p-4 shadow">
        <h2 class="mb-4 text-center text-danger">Editar Producto</h2>
        <form action="editar_producto.php?id=<?= htmlspecialchars($producto['idProducto']) ?>" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
            <input type="hidden" name="idProducto" value="<?= htmlspecialchars($producto['idProducto']) ?>">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="nombreProducto" class="form-label">Nombre del Producto</label>
                    <input type="text" class="form-control" id="nombreProducto" name="nombreProducto" value="<?= htmlspecialchars($producto['nombreProducto']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="talla" class="form-label">Talla</label>
                    <input type="text" class="form-control" id="talla" name="talla" value="<?= htmlspecialchars($producto['talla']) ?>" required>
                </div>
            </div>
            <div class="mb-3">
                <label for="descripcion" class="form-label">Descripción</label>
                <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required><?= htmlspecialchars($producto['descripcion']) ?></textarea>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="precio" class="form-label">Precio (€)</label>
                    <input type="number" class="form-control" id="precio" name="precio" step="0.01" value="<?= htmlspecialchars($producto['precio']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="condicion" class="form-label">Condición</label>
                    <input type="text" class="form-control" id="condicion" name="condicion" value="<?= htmlspecialchars($producto['condicion']) ?>" required>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="idCategoria" class="form-label">Categoría</label>
                    <select class="form-select" id="idCategoria" name="idCategoria" required>
                        <?php foreach ($categorias as $categoria): ?>
                            <option value="<?= $categoria['idCategoria'] ?>" <?= $categoria['idCategoria'] == $producto['idCategoria'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($categoria['nombreCategoria']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label for="fotos1" class="form-label">Foto 1</label>
                <input type="file" class="form-control" id="fotos1" name="fotos[]" onchange="previewFotos(event, 'preview1')">
                <div id="preview1" class="mb-3"></div>
            </div>
            <div class="mb-3">
                <label for="fotos2" class="form-label">Foto 2</label>
                <input type="file" class="form-control" id="fotos2" name="fotos[]" onchange="previewFotos(event, 'preview2')">
                <div id="preview2" class="mb-3"></div>
            </div>
            <div class="mb-3">
                <label for="fotos3" class="form-label">Foto 3</label>
                <input type="file" class="form-control" id="fotos3" name="fotos[]" onchange="previewFotos(event, 'preview3')">
                <div id="preview3" class="mb-3"></div>
            </div>
            <div class="mt-2">
                <strong>Fotos actuales:</strong>
                <?php
                if (isset($fotos) && count($fotos) > 0) {
                    foreach ($fotos as $foto) {
                        echo '<img src="' . htmlspecialchars($foto['nombreFoto']) . '" alt="Foto del producto" class="img-thumbnail" style="width: 100px; height: 100px;"> ';
                    }
                } else {
                    echo "No hay fotos actuales.";
                }
                ?>
            </div>
            <div class="d-flex justify-content-between">
                <a href="miperfil.php" class="btn btn-dark">Volver</a>
                <button type="submit" class="btn btn-danger">Actualizar Producto</button>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
function previewFotos(event, previewId) {
    var reader = new FileReader();
    reader.onload = function() {
        var output = document.getElementById(previewId);
        output.innerHTML = '<img src="' + reader.result + '" class="img-thumbnail" style="width: 100px; height: 100px;">';
    };
    reader.readAsDataURL(event.target.files[0]);
}
</script>
