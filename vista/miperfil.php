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

// Función para borrar productos o cambiar el estado
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['idProducto'])) {
    $idProducto = $_POST['idProducto'];

    if ($_POST['action'] == 'borrar') {
        try {
            // Iniciar una transacción
            $conn->beginTransaction();

            // Eliminar las fotos asociadas al producto
            $stmt = $conn->prepare("DELETE FROM fotos WHERE idProducto = ?");
            $stmt->execute([$idProducto]);

            // Eliminar las transacciones asociadas al producto
            $stmt = $conn->prepare("DELETE FROM transacciones WHERE idProducto = ?");
            $stmt->execute([$idProducto]);

            // Luego eliminar el producto
            $stmt = $conn->prepare("DELETE FROM productos WHERE idProducto = ? AND idUsuario = ?");
            $stmt->execute([$idProducto, $_SESSION['user_id']]);
            $deleted = $stmt->rowCount();

            if ($deleted) {
                $conn->commit();
                $success_message = "Producto borrado con éxito.";
            } else {
                $conn->rollBack();
                $error_message = "Error al borrar el producto o no tienes permiso para borrar este producto.";
            }
        } catch (Exception $e) {
            $conn->rollBack();
            $error_message = "Error al borrar el producto: " . $e->getMessage();
        }
    } elseif ($_POST['action'] == 'cambiarEstado' && isset($_POST['nuevoEstado'])) {
        try {
            $nuevoEstado = $_POST['nuevoEstado'];
            $stmt = $conn->prepare("UPDATE transacciones SET estado = ? WHERE idProducto = ?");
            $stmt->execute([$nuevoEstado, $idProducto]);
            $success_message = "Estado del producto actualizado a '$nuevoEstado'.";
        } catch (Exception $e) {
            $error_message = "Error al cambiar el estado del producto: " . $e->getMessage();
        }
    }
}

try {
    // Obtener información del usuario
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE idUsuario = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        header('Location: login.php');
        exit();
    }

    // Obtener calificación del usuario
    $stmt = $conn->prepare("SELECT AVG(valoracion) as ratingAverage, COUNT(*) as totalRatings FROM valoraciones WHERE idValorado = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $ratings = $stmt->fetch(PDO::FETCH_ASSOC);
    $ratingAverage = isset($ratings['ratingAverage']) ? $ratings['ratingAverage'] : 0;
    $totalRatings = isset($ratings['totalRatings']) ? $ratings['totalRatings'] : 0;

} catch (Exception $e) {
    echo "Error al obtener los datos del perfil: " . $e->getMessage();
}

// Función para obtener la calificación en estrellas
function getStarRating($rating) {
    $stars = '';
    for ($i = 0; $i < 5; $i++) {
        if ($i < floor($rating)) {
            $stars .= '<i class="fas fa-star"></i>';
        } elseif ($i < ceil($rating)) {
            $stars .= '<i class="fas fa-star-half-alt"></i>';
        } else {
            $stars .= '<i class="far fa-star"></i>';
        }
    }
    return $stars;
}
?>

<?php include '../includes/header.php'; ?>

<div class="perfil container mt-5">
    <div class="row">
        <div class="col-md-3 text-center">
            <div class="profile-icon-wrapper">
                <?php 
                $profileImagePath = htmlspecialchars($user['foto']);
                if (!empty($user['foto']) && file_exists($profileImagePath)): ?>
                    <img src="<?php echo $profileImagePath; ?>" alt="Foto de perfil" class="img-fluid rounded-circle mb-3" style="width: 150px; height: 150px;">
                <?php else: ?>
                    <i class="fas fa-user-circle fa-9x text-muted"></i>
                <?php endif; ?>
            </div>
            <h4><?php echo htmlspecialchars($user['nombreUsuario']); ?></h4>
            <p><?php echo ($totalRatings > 0) ? getStarRating($ratingAverage) . " " . number_format($ratingAverage, 1) . " de 5 (de $totalRatings valoraciones)" : "Aún no hay valoraciones"; ?></p>
        </div>
        <div class="col-md-9">
            <p><?php echo htmlspecialchars($user['descripcion']); ?></p>
            <h5>Información verificada:</h5>
            <p><i class="fas fa-check-circle"></i> Google</p>
            <p><i class="fas fa-check-circle"></i> E-mail</p>
        </div>
    </div>
    
    <hr>
    <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link active" id="productos-tab" data-bs-toggle="tab" href="#productos" role="tab" aria-controls="productos" aria-selected="true">Mis Productos</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="vendidos-tab" data-bs-toggle="tab" href="#vendidos" role="tab" aria-controls="vendidos" aria-selected="false">Vendidos</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="comprados-tab" data-bs-toggle="tab" href="#comprados" role="tab" aria-controls="comprados" aria-selected="false">Comprados</a>
        </li>
    </ul>

    <div class="tab-content" id="myTabContent">
        <!-- Pestaña Mis Productos -->
        <div class="tab-pane fade show active" id="productos" role="tabpanel" aria-labelledby="productos-tab">
            <?php
            // Obtener productos en venta y reservados
            $stmt = $conn->prepare("SELECT p.*, f.nombreFoto, t.estado 
                                    FROM productos p 
                                    LEFT JOIN fotos f ON p.idProducto = f.idProducto 
                                    LEFT JOIN transacciones t ON p.idProducto = t.idProducto 
                                    WHERE t.idVendedor = ? AND (t.estado = 'enventa' OR t.estado = 'reservado') 
                                    GROUP BY p.idProducto");
            $stmt->execute([$_SESSION['user_id']]);
            $productsForSale = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ?>
            <div class="d-flex justify-content-end mt-3">
                <a href="subir_producto.php" class="btn btn-primary btn-subir-productos">Subir productos</a>
            </div>
            <h5 class="mt-3">Productos en Venta</h5>
            <div class="row mt-3">
                <?php if (!empty($productsForSale)): ?>
                    <?php foreach ($productsForSale as $product): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card p-card">
                                <div class="product-image-wrapper">
                                    <?php if (!empty($product['nombreFoto'])): ?>
                                        <img src="<?php echo htmlspecialchars($product['nombreFoto']); ?>" class="card-img-top product-image" alt="Foto del producto">
                                    <?php else: ?>
                                        <p>No hay fotos disponibles</p>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <p class="card-text"><strong>Precio:</strong> €<?php echo htmlspecialchars($product['precio']); ?></p>
                                            <p class="card-text"><strong>Talla:</strong> <?php echo htmlspecialchars($product['talla']); ?></p>
                                            <?php if ($product['estado'] == 'reservado'): ?>
                                                <p class="text-warning"><i class="fas fa-clock"></i> Reservado</p>
                                            <?php elseif ($product['estado'] == 'vendido'): ?>
                                                <p class="text-success"><i class="fas fa-check-circle"></i> Vendido</p>
                                            <?php endif; ?>
                                        </div>
                                        <div class="dropdown">
                                            <a class="text-danger" href="#" id="dropdownMenuLink<?php echo $product['idProducto']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fas fa-bars"></i>
                                            </a>
                                            <ul class="dropdown-menu dropdown-menu-left" aria-labelledby="dropdownMenuLink<?php echo $product['idProducto']; ?>">
                                                <li><a class="dropdown-item" style="color: red;" href="editar_producto.php?id=<?php echo $product['idProducto']; ?>">Editar</a></li>
                                                <li><a class="dropdown-item" style="color: red;" href="#" onclick="borrarProducto('<?php echo $product['idProducto']; ?>')">Borrar</a></li>
                                                <?php if ($product['estado'] != 'vendido'): ?>
                                                    <li><a class="dropdown-item" style="color: red;" href="#" onclick="cambiarEstado('<?php echo $product['idProducto']; ?>', 'reservado')">Reservar</a></li>
                                                <?php endif; ?>
                                                <?php if ($product['estado'] == 'reservado'): ?>
                                                    <li><a class="dropdown-item" style="color: red;" href="#" onclick="cambiarEstado('<?php echo $product['idProducto']; ?>', 'enventa')">Volver a poner en venta</a></li>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center">
                        <p>No tienes productos en venta.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Pestaña Productos Vendidos -->
        <div class="tab-pane fade" id="vendidos" role="tabpanel" aria-labelledby="vendidos-tab">
            <?php
            // Obtener productos vendidos
            $stmt = $conn->prepare("SELECT p.*, f.nombreFoto 
                                    FROM productos p 
                                    LEFT JOIN fotos f ON p.idProducto = f.idProducto 
                                    LEFT JOIN transacciones t ON p.idProducto = t.idProducto 
                                    WHERE t.idVendedor = ? AND t.estado = 'vendido' 
                                    GROUP BY p.idProducto");
            $stmt->execute([$_SESSION['user_id']]);
            $productsSold = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ?>
            <h5 class="mt-3">Productos Vendidos</h5>
            <div class="row mt-3">
                <?php if (!empty($productsSold)): ?>
                    <?php foreach ($productsSold as $product): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card p-card">
                                <div class="product-image-wrapper">
                                    <?php if (!empty($product['nombreFoto'])): ?>
                                        <img src="<?php echo htmlspecialchars($product['nombreFoto']); ?>" class="card-img-top product-image" alt="Foto del producto">
                                    <?php else: ?>
                                        <p>No hay fotos disponibles</p>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <p class="card-text"><strong>Precio:</strong> €<?php echo htmlspecialchars($product['precio']); ?></p>
                                            <p class="card-text"><strong>Talla:</strong> <?php echo htmlspecialchars($product['talla']); ?></p>
                                        </div>
                                        <div class="dropdown">
                                            <a class="text-danger" href="#" id="dropdownMenuLink<?php echo $product['idProducto']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fas fa-bars"></i>
                                            </a>
                                            <ul class="dropdown-menu dropdown-menu-left" aria-labelledby="dropdownMenuLink<?php echo $product['idProducto']; ?>">
                                                <li><a class="dropdown-item" style="color: red;" href="#" onclick="borrarProducto('<?php echo $product['idProducto']; ?>')">Borrar</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center">
                        <p>No has vendido productos.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Pestaña Productos Comprados -->
        <div class="tab-pane fade" id="comprados" role="tabpanel" aria-labelledby="comprados-tab">
            <?php
            // Obtener productos comprados por el usuario (estado 'comprado')
            $stmt = $conn->prepare("SELECT p.*, f.nombreFoto, t.fechaTransaccion 
                                    FROM productos p
                                    LEFT JOIN fotos f ON p.idProducto = f.idProducto
                                    INNER JOIN transacciones t ON p.idProducto = t.idProducto
                                    WHERE t.idComprador = ? AND t.estado = 'vendido' 
                                    GROUP BY p.idProducto");
            $stmt->execute([$_SESSION['user_id']]);
            $purchases = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ?>
            <h5 class="mt-3">Productos Comprados</h5>
            <div class="row mt-3">
                <?php if (!empty($purchases)): ?>
                    <?php foreach ($purchases as $purchase): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card p-card">
                                <div class="product-image-wrapper">
                                    <?php if (!empty($purchase['nombreFoto'])): ?>
                                        <img src="<?php echo htmlspecialchars($purchase['nombreFoto']); ?>" class="card-img-top product-image" alt="Foto del producto">
                                    <?php else: ?>
                                        <p>No hay fotos disponibles</p>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <p class="card-text"><strong>Precio:</strong> €<?php echo htmlspecialchars($purchase['precio']); ?></p>
                                            <p class="card-text"><strong>Talla:</strong> <?php echo htmlspecialchars($purchase['talla']); ?></p>
                                            <p class="card-text"><strong>Fecha de Compra:</strong> <?php echo htmlspecialchars($purchase['fechaTransaccion']); ?></p>
                                        </div>
                                        <div class="dropdown">
                                            <a class="text-danger" href="#" id="dropdownMenuLink<?php echo $purchase['idProducto']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fas fa-bars"></i>
                                            </a>
                                            <ul class="dropdown-menu dropdown-menu-left" aria-labelledby="dropdownMenuLink<?php echo $purchase['idProducto']; ?>">
                                               <li><a class="dropdown-item" style="color: red;" href="#" onclick="borrarProducto('<?php echo $purchase['idProducto']; ?>')">Borrar</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center">
                        <p>No has realizado ninguna compra.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>


<script>
function borrarProducto(idProducto) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "¡No podrás revertir esto!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, bórralo!'
    }).then((result) => {
        if (result.isConfirmed) {
            var form = document.createElement('form');
            form.method = 'post';
            form.action = '';
            
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'idProducto';
            input.value = idProducto;
            
            var action = document.createElement('input');
            action.type = 'hidden';
            action.name = 'action';
            action.value = 'borrar';
            
            form.appendChild(input);
            form.appendChild(action);
            document.body.appendChild(form);
            form.submit();
        }
    });
}

function cambiarEstado(idProducto, nuevoEstado) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "¡Vas a cambiar el estado del producto!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, cambiarlo!'
    }).then((result) => {
        if (result.isConfirmed) {
            var form = document.createElement('form');
            form.method = 'post';
            form.action = '';
            
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'idProducto';
            input.value = idProducto;
            
            var action = document.createElement('input');
            action.type = 'hidden';
            action.name = 'action';
            action.value = 'cambiarEstado';
            
            var estado = document.createElement('input');
            estado.type = 'hidden';
            estado.name = 'nuevoEstado';
            estado.value = nuevoEstado;
            
            form.appendChild(input);
            form.appendChild(action);
            form.appendChild(estado);
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>
