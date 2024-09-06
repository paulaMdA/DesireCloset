<?php
require_once '../config/conexion.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$user_id = isset($_GET['id']) ? intval($_GET['id']) : null;
$logged_in_user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

if (!$user_id) {
    echo "Usuario no encontrado.";
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Obtener información del usuario
$stmt = $db->prepare("SELECT * FROM usuarios WHERE idUsuario = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "Usuario no encontrado.";
    exit();
}

// Obtener calificación del usuario
$stmt = $db->prepare("SELECT AVG(valoracion) as ratingAverage, COUNT(*) as totalRatings FROM valoraciones WHERE idValorado = ?");
$stmt->execute([$user_id]);
$ratings = $stmt->fetch(PDO::FETCH_ASSOC);
$ratingAverage = isset($ratings['ratingAverage']) ? $ratings['ratingAverage'] : 0;
$totalRatings = isset($ratings['totalRatings']) ? $ratings['totalRatings'] : 0;

// Verificar si el usuario ya ha valorado
$user_has_rated = false;
if ($logged_in_user_id) {
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM valoraciones WHERE idValorado = ? AND idValorador = ?");
    $stmt->execute([$user_id, $logged_in_user_id]);
    $user_has_rated = $stmt->fetchColumn() > 0;
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

<div class="modal-header">
    <h5 class="modal-title">Perfil de Usuario</h5>
    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<div class="modal-body">
    <div class="text-center">
        <?php if (!empty($user['foto']) && file_exists($user['foto'])): ?>
            <img src="<?php echo htmlspecialchars($user['foto']); ?>" alt="Foto de perfil" class="img-fluid rounded-circle mb-3" style="width: 150px; height: 150px;">
        <?php else: ?>
            <i class="fas fa-user-circle fa-9x text-muted"></i>
        <?php endif; ?>
        <h4><?php echo htmlspecialchars($user['nombreUsuario']); ?></h4>
        <p><?php echo ($totalRatings > 0) ? getStarRating($ratingAverage) . " " . number_format($ratingAverage, 1) . " de 5 (de $totalRatings valoraciones)" : "Aún no hay valoraciones"; ?></p>
    </div>
    <?php if ($logged_in_user_id): ?>
        <?php if ($user_has_rated): ?>
            <p class="text-center">Ya has valorado a este usuario.</p>
        <?php else: ?>
            <form action="guardar_valoracion.php" method="POST">
                <div class="form-group">
                    <label for="rating">Valoración:</label>
                    <select class="form-control" id="rating" name="rating">
                        <option value="1">1 estrella</option>
                        <option value="2">2 estrellas</option>
                        <option value="3">3 estrellas</option>
                        <option value="4">4 estrellas</option>
                        <option value="5">5 estrellas</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="comentario">Comentario:</label>
                    <textarea class="form-control" id="comentario" name="comentario" rows="3"></textarea>
                </div>
                <input type="hidden" name="idValorado" value="<?php echo $user_id; ?>">
                <button type="submit" class="btn btn-primary">Enviar Valoración</button>
            </form>
        <?php endif; ?>
    <?php else: ?>
        <p class="text-center">Debes iniciar sesión para valorar a este usuario.</p>
    <?php endif; ?>
</div>
