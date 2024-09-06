<?php
session_start();
require_once '../config/conexion.php';

$database = new Database();
$db = $database->getConnection();

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$user_role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'invitado'; 

$query = "SELECT p.*, u.nombreUsuario, u.foto as fotoUsuario, f.nombreFoto, t.estado,
          (SELECT COUNT(*) FROM megusta mg WHERE mg.idProducto = p.idProducto) AS likeCount,
          (SELECT COUNT(*) FROM megusta mg WHERE mg.idProducto = p.idProducto AND mg.idUsuario = ?) AS userLikeCount
          FROM productos p
          JOIN usuarios u ON p.idUsuario = u.idUsuario
          LEFT JOIN (SELECT nombreFoto, idProducto FROM fotos GROUP BY idProducto) f ON p.idProducto = f.idProducto
          LEFT JOIN transacciones t ON p.idProducto = t.idProducto
          WHERE p.idCategoria = (SELECT idCategoria FROM categorias WHERE nombreCategoria = 'Bragas y Tangas')
          AND (t.estado IS NULL OR t.estado != 'vendido')
          GROUP BY p.idProducto";

$stmt = $db->prepare($query);
$stmt->execute([$user_id]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>

<div class="ver container mt-5">
    <div style="background-color: black; color: white; padding: 10px; margin-bottom: 20px;">
        <h2 class="mb-4" style="text-transform: uppercase; font-weight: bold; text-align: center;"> Bragas y Tangas</h2>
    </div>
    <div class="row">
        <?php foreach ($products as $product): ?>
            <div class="col-md-3 mb-4">
                <div class="card">
                    <div style="position: relative; width: 100%; height: 250px; overflow: hidden;">
                        <?php if (!empty($product['nombreFoto'])): ?>
                            <img src="<?php echo htmlspecialchars($product['nombreFoto']); ?>" class="card-img-top" alt="Foto del producto" style="width: 100%; height: 100%; object-fit: cover;">
                        <?php else: ?>
                            <img src="../assets/img/default.png" class="card-img-top" alt="Sin imagen" style="width: 100%; height: 100%; object-fit: cover;">
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
                            <button class="btn btn-outline-dark" onclick="openModal(<?php echo $product['idProducto']; ?>)">
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
</div>

<!-- Modal Structure -->
<div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!-- Content loaded dynamically -->
        </div>
    </div>
</div>

<script>
function openModal(productId) {
    // Fetch product details via AJAX
    fetch('detalles_productos.php?id=' + productId)
        .then(response => response.text())
        .then(html => {
            document.querySelector('#productModal .modal-content').innerHTML = html;
            new bootstrap.Modal(document.getElementById('productModal')).show();
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
