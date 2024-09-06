<?php
require_once '../config/conexion.php';

// Verifica si se ha proporcionado el ID del usuario
if (isset($_GET['id'])) {
    $userId = $_GET['id'];

    // Obtener detalles del usuario
    $query = "SELECT nombreUsuario, foto, descripcion, fechaNacimiento FROM usuarios WHERE idUsuario = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$userId]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // Calcular el promedio de las valoraciones
    $query = "SELECT AVG(valoracion) as ratingAverage FROM valoraciones WHERE idValorado = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$userId]);
    $ratingAverageResult = $stmt->fetch(PDO::FETCH_ASSOC);
    $ratingAverage = $ratingAverageResult['ratingAverage'] ?? 0;

    // Obtener todas las reseñas del usuario
    $query = "SELECT v.valoracion, v.comentario, u.nombreUsuario AS valorador
              FROM valoraciones v
              JOIN usuarios u ON v.idValorador = u.idUsuario
              WHERE v.idValorado = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$userId]);
    $resenas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Definir la función para obtener la calificación en estrellas
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

    // Función para calcular la edad
    function calculateAge($birthDate) {
        $birthDate = new DateTime($birthDate);
        $today = new DateTime('today');
        $age = $birthDate->diff($today)->y;
        return $age;
    }

    include '../includes/header.php';
    ?>
    <div class="container mt-5">
        <h2 class="text-danger">Reseñas de <?php echo htmlspecialchars($usuario['nombreUsuario']); ?></h2>
        <div class="text-center mb-4">
            <?php 
            $fotoPath = htmlspecialchars($usuario['foto']);
            if (!empty($usuario['foto']) && file_exists($fotoPath)): ?>
                <img src="<?php echo $fotoPath; ?>" class="img-fluid rounded-circle mb-2" alt="<?php echo htmlspecialchars($usuario['nombreUsuario']); ?>" style="width: 150px; height: 150px;">
            <?php else: ?>
                <img src="../assets/uploads/default-profile.png" class="img-fluid rounded-circle mb-2" alt="Perfil por defecto" style="width: 150px; height: 150px;">
            <?php endif; ?>
            <div>
                <p class="text-muted mb-0"><?php echo calculateAge($usuario['fechaNacimiento']); ?> años</p>
                <p class="text-muted"><?php echo getStarRating($ratingAverage); ?></p>
            </div>
        </div>
        <p><strong>Descripción:</strong> <?php echo htmlspecialchars($usuario['descripcion']); ?></p>
        <?php if ($resenas): ?>
            <div class="list-group">
                <?php foreach ($resenas as $resena): ?>
                    <div class="list-group-item mb-3" style="background-color: #f8f9fa; border: 1px solid #ddd;">
                        <h5 class="mb-1 text-danger"><?php echo htmlspecialchars($resena['valorador']); ?></h5>
                        <div class="mb-1"><?php echo getStarRating($resena['valoracion']); ?></div>
                        <p class="mb-1"><?php echo htmlspecialchars($resena['comentario']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-danger">No hay reseñas para este usuario.</p>
        <?php endif; ?>
        <div class="mt-4">
            <a href="javascript:history.back()" class="btn btn-danger"><i class="fas fa-arrow-left"></i> Volver</a>
        </div>
    </div>
    <?php
    include '../includes/footer.php';
} else {
    echo "<p class='text-danger'>No se proporcionó un ID de usuario.</p>";
}
?>
