<?php
session_start();
require_once '../config/conexion.php';

$database = new Database();
$db = $database->getConnection();

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$user_role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'invitado'; // Asigna 'invitado' por defecto si no está autenticado

$searchTerm = isset($_GET['busqueda']) ? $_GET['busqueda'] : '';

$products = [];
$users = [];

if (!empty($searchTerm)) {
    // Buscar usuarios
    $stmt = $db->prepare("SELECT u.*, ur.idRol, 
                            (SELECT AVG(valoracion) FROM valoraciones WHERE idValorado = u.idUsuario) as ratingAverage 
                          FROM usuarios u
                          JOIN usuarios_roles ur ON u.idUsuario = ur.idUsuario
                          WHERE (u.nombreUsuario LIKE ? OR u.nombre LIKE ?)
                          AND u.pagado = 1 AND ur.idRol = 2 AND u.fechaBaja IS NULL");
    $stmt->execute(['%' . $searchTerm . '%', '%' . $searchTerm . '%']);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Buscar productos
    $stmt = $db->prepare("SELECT p.*, u.nombreUsuario, u.foto as fotoUsuario, f.nombreFoto, t.estado,
                          (SELECT COUNT(*) FROM megusta mg WHERE mg.idProducto = p.idProducto) AS likeCount,
                          (SELECT COUNT(*) FROM megusta mg WHERE mg.idProducto = p.idProducto AND mg.idUsuario = ?) AS userLikeCount
                          FROM productos p
                          JOIN usuarios u ON p.idUsuario = u.idUsuario
                          LEFT JOIN (SELECT nombreFoto, idProducto FROM fotos GROUP BY idProducto) f ON p.idProducto = f.idProducto
                          LEFT JOIN transacciones t ON p.idProducto = t.idProducto
                          WHERE (p.nombreProducto LIKE ? OR p.descripcion LIKE ?)
                          AND (t.estado IS NULL OR t.estado != 'vendido')
                          GROUP BY p.idProducto");
    $stmt->execute([$user_id, '%' . $searchTerm . '%', '%' . $searchTerm . '%']);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Fetch all products if no search term
    $stmt = $db->prepare("SELECT p.*, u.nombreUsuario, u.foto as fotoUsuario, f.nombreFoto, t.estado,
                          (SELECT COUNT(*) FROM megusta mg WHERE mg.idProducto = p.idProducto) AS likeCount,
                          (SELECT COUNT(*) FROM megusta mg WHERE mg.idProducto = p.idProducto AND mg.idUsuario = ?) AS userLikeCount
                          FROM productos p
                          JOIN usuarios u ON p.idUsuario = u.idUsuario
                          LEFT JOIN (SELECT nombreFoto, idProducto FROM fotos GROUP BY idProducto) f ON p.idProducto = f.idProducto
                          LEFT JOIN transacciones t ON p.idProducto = t.idProducto
                          WHERE t.estado IS NULL OR t.estado != 'vendido'
                          GROUP BY p.idProducto");
    $stmt->execute([$user_id]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch all users if no search term
    $stmt = $db->prepare("SELECT u.*, ur.idRol, 
                            (SELECT AVG(valoracion) FROM valoraciones WHERE idValorado = u.idUsuario) as ratingAverage 
                          FROM usuarios u
                          JOIN usuarios_roles ur ON u.idUsuario = ur.idUsuario
                          WHERE u.pagado = 1 AND ur.idRol = 2 AND u.fechaBaja IS NULL");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

include '../includes/header.php';

function getStarRating($rating) {
    $stars = '';
    for ($i = 0; $i < 5; $i++) {
        if ($i < floor($rating)) {
            $stars .= '<i class="fas fa-star text-warning"></i>';
        } elseif ($i < ceil($rating)) {
            $stars .= '<i class="fas fa-star-half-alt text-warning"></i>';
        } else {
            $stars .= '<i class="far fa-star text-warning"></i>';
        }
    }
    return $stars;
}
?>

<div class="ver container mt-5">
    <?php if (empty($products) && empty($users)): ?>
        <div class="row">
            <div class="col-12">
                <p class="text-center">No se encontraron resultados para "<?php echo htmlspecialchars($searchTerm); ?>"</p>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($products)): ?>
        <div style="background-color: black; color: white; padding: 5px; margin-bottom: 20px;">
            <h2 class="mb-4" style="text-transform: uppercase; font-weight: bold; text-align: center;">Todos los productos</h2>
        </div>
        <div class="row">
            <?php foreach ($products as $product): ?>
                <div class="col-md-3 mb-4">
                    <div class="card">
                        <div style="position: relative; width: 100%; height: 250px; overflow: hidden;">
                            <?php if (!empty($product['nombreFoto'])): ?>
                                <img src="<?php echo htmlspecialchars($product['nombreFoto']); ?>" class="card-img-top product-image" alt="Foto del producto" style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                <img src="../assets/img/default.png" class="card-img-top product-image" alt="Sin imagen" style="width: 100%; height: 100%; object-fit: cover;">
                            <?php endif; ?>
                            <?php if (!empty($product['fotoUsuario']) && file_exists($product['fotoUsuario'])): ?>
                                <img src="<?php echo htmlspecialchars($product['fotoUsuario']); ?>" class="rounded-circle" alt="Foto de usuario" style="position: absolute; top: 10px; left: 10px; width: 50px; height: 50px; border: 2px solid white;">
                            <?php else: ?>
                                <img src="../assets/img/default-user.png" class="rounded-circle" alt="Foto de usuario" style="position: absolute; top: 10px; left: 10px; width: 50px; height: 50px; border: 2px solid white;">
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title" style="font-weight: bold; text-transform: uppercase;"><?php echo htmlspecialchars($product['nombreProducto']); ?></h5>
                            <p class="card-text"><strong>Precio:</strong> €<?php echo htmlspecialchars($product['precio']); ?></p>
                            <p class="card-text"><strong>Talla:</strong> <?php echo htmlspecialchars($product['talla']); ?></p>
                            <?php if ($product['estado'] == 'reservado'): ?>
                                <p class="text-warning"><i class="fas fa-clock"></i> Reservado</p>
                            <?php endif; ?>
                            <div class="d-flex justify-content-between align-items-center">
                                <button class="btn btn-outline like-button" data-product-id="<?php echo $product['idProducto']; ?>" data-user-liked="<?php echo $product['userLikeCount'] > 0 ? 'true' : 'false'; ?>">
                                    <i class="fas fa-heart" style="color: <?php echo $product['userLikeCount'] > 0 ? 'red' : 'black'; ?>"></i> <span id="like-count-<?php echo $product['idProducto']; ?>" style="color: red;"><?php echo $product['likeCount']; ?></span>
                                </button>
                                <button class="btn btn-outline-dark" onclick="openProductModal(<?php echo $product['idProducto']; ?>)">
                                    <i class="fas fa-info-circle" style="color: red;"></i> Info
                                </button>
                                <?php if ($user_role == '2'): // Sólo mostrar botón de compra si el rol es 2 ?>
                                    <button class="btn btn-primary" onclick="window.location.href='pagar.php?id=<?php echo $product['idProducto']; ?>'">Comprar</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($users)): ?>
        <div style="background-color: black; color: white; padding: 5px; margin-bottom: 20px;">
            <h2 class="mb-4" style="text-transform: uppercase; font-weight: bold; text-align: center;">Usuarios</h2>
        </div>
        <div class="row">
            <?php foreach ($users as $user): ?>
                <div class="col-md-3 text-center mb-4">
                    <a href="#" onclick="openUserModal(<?php echo $user['idUsuario']; ?>)">
                        <?php if (!empty($user['foto']) && file_exists($user['foto'])): ?>
                            <img src="<?php echo htmlspecialchars($user['foto']); ?>" class="img-fluid rounded-circle mb-2" alt="<?php echo htmlspecialchars($user['nombreUsuario']); ?>" style="width: 150px; height: 150px;">
                        <?php else: ?>
                            <img src="../assets/uploads/default-profile.png" class="img-fluid rounded-circle mb-2" alt="Default Profile" style="width: 150px; height: 150px;">
                        <?php endif; ?>
                    </a>
                    <h5 class="text-danger"><?php echo htmlspecialchars($user['nombreUsuario']); ?></h5>
                    <p class="text-muted"><?php echo getStarRating($user['ratingAverage']); ?></p>
                    <p class="text-muted"><?php echo htmlspecialchars($user['descripcion']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Modal Structure for Products -->
<div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!-- Content loaded dynamically -->
        </div>
    </div>
</div>

<!-- Modal Structure for Users -->
<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <!-- Content loaded dynamically -->
        </div>
    </div>
</div>

<script>
function openProductModal(productId) {
    // Fetch product details via AJAX
    fetch('detalles_productos.php?id=' + productId)
        .then(response => response.text())
        .then(html => {
            document.querySelector('#productModal .modal-content').innerHTML = html;
            new bootstrap.Modal(document.getElementById('productModal')).show();
        });
}

function openUserModal(userId) {
    // Fetch user details via AJAX
    fetch('detalles_usuarios.php?id=' + userId)
        .then(response => response.text())
        .then(html => {
            document.querySelector('#userModal .modal-content').innerHTML = html;
            new bootstrap.Modal(document.getElementById('userModal')).show();
        });
}

// Like button functionality
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.like-button').forEach(function(button) {
        button.addEventListener('click', function() {
            let productId = this.getAttribute('data-product-id');
            let userLiked = this.getAttribute('data-user-liked') === 'true';
            let likeCountElem = document.getElementById('like-count-' + productId);
            let likeButton = this;
            let currentCount = parseInt(likeCountElem.textContent);
            
            let url = 'like_product.php'; // Ruta al archivo PHP que maneja las solicitudes de "Me Gusta"
            let formData = new FormData();
            formData.append('idProducto', productId);
            formData.append('action', userLiked ? 'unlike' : 'like');

            fetch(url, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (userLiked) {
                        likeCountElem.textContent = currentCount - 1;
                        likeButton.querySelector('i').style.color = 'black';
                        likeButton.setAttribute('data-user-liked', 'false');
                    } else {
                        likeCountElem.textContent = currentCount + 1;
                        likeButton.querySelector('i').style.color = 'red';
                        likeButton.setAttribute('data-user-liked', 'true');
                    }
                }
            })
            .catch(error => console.error('Error:', error));
        });
    });
});
</script>

<?php include '../includes/footer.php'; ?>
