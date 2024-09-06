<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../config/conexion.php';

if (!isset($_GET['id'])) {
    echo 'Producto no encontrado.';
    exit();
}

$productId = $_GET['id'];

$database = new Database();
$db = $database->getConnection();

// Fetch product details and photos
$query = "SELECT p.*, u.nombreUsuario, u.foto as fotoUsuario, f.nombreFoto 
          FROM productos p
          JOIN usuarios u ON p.idUsuario = u.idUsuario
          LEFT JOIN fotos f ON p.idProducto = f.idProducto
          WHERE p.idProducto = ?";
$stmt = $db->prepare($query);
$stmt->execute([$productId]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    echo 'Producto no encontrado.';
    exit();
}

// Fetch all photos of the product
$photoQuery = "SELECT nombreFoto FROM fotos WHERE idProducto = ?";
$photoStmt = $db->prepare($photoQuery);
$photoStmt->execute([$productId]);
$photos = $photoStmt->fetchAll(PDO::FETCH_ASSOC);

// Check user role and ownership
$userId = $_SESSION['user_id'] ?? null;
$canBuy = false;
$isOwner = false;

if ($userId) {
    $userRoleQuery = "SELECT idRol FROM usuarios_roles WHERE idUsuario = ?";
    $userRoleStmt = $db->prepare($userRoleQuery);
    $userRoleStmt->execute([$userId]);
    $userRole = $userRoleStmt->fetch(PDO::FETCH_ASSOC);

    if ($userRole && $userRole['idRol'] == 2) {
        $canBuy = true;
    }

    if ($product['idUsuario'] == $userId) {
        $isOwner = true;
    }
}
?>

<div class="modal-header">
    <h5 class="modal-title" style="font-weight: bold; text-transform: uppercase;"><?php echo htmlspecialchars($product['nombreProducto']); ?></h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <div class="row">
        <div class="col-md-6">
            <div id="productCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <?php foreach ($photos as $index => $photo): ?>
                        <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                            <img src="<?php echo htmlspecialchars($photo['nombreFoto']); ?>" class="d-block w-100" alt="Foto del producto" style="height: 500px; object-fit: cover;">
                        </div>
                    <?php endforeach; ?>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#productCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Anterior</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#productCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Siguiente</span>
                </button>
            </div>
        </div>
        <div class="col-md-6">
            <form>
                <div class="mb-3">
                    <label for="nombreProducto" class="form-label">Nombre del Producto</label>
                    <input type="text" class="form-control" id="nombreProducto" value="<?php echo htmlspecialchars($product['nombreProducto']); ?>" disabled>
                </div>
                <div class="mb-3">
                    <label for="precio" class="form-label">Precio</label>
                    <input type="text" class="form-control" id="precio" value="€<?php echo htmlspecialchars($product['precio']); ?>" disabled>
                </div>
                <div class="mb-3">
                    <label for="talla" class="form-label">Talla</label>
                    <input type="text" class="form-control" id="talla" value="<?php echo htmlspecialchars($product['talla']); ?>" disabled>
                </div>
                <div class="mb-3">
                    <label for="descripcion" class="form-label">Descripción</label>
                    <textarea class="form-control" id="descripcion" rows="3" disabled><?php echo htmlspecialchars($product['descripcion']); ?></textarea>
                </div>
                <?php if ($canBuy && !$isOwner): ?>
                    <button type="button" class="btn btn-primary" onclick="window.location.href='pago_producto.php?id=<?php echo $productId; ?>'">Comprar</button>
                <?php elseif ($isOwner): ?>
                    <p class="text-warning">No puedes comprar tu propio producto.</p>
                <?php else: ?>
                    <p class="text-danger">Solo si estas subscrito puedes comprar este producto.</p>
                <?php endif; ?>
            </form>
        </div>
    </div>
</div>
