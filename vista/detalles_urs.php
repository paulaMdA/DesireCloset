<?php
require_once '../config/conexion.php';

session_start();

if (isset($_GET['id'])) {
    $userId = $_GET['id'];

    // Obtener detalles del usuario
    $query = "SELECT u.nombreUsuario, u.foto, u.descripcion, u.fechaNacimiento,
                     (SELECT AVG(valoracion) FROM valoraciones WHERE idValorado = u.idUsuario) as ratingAverage
              FROM usuarios u
              WHERE u.idUsuario = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$userId]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        function calculateAge($birthDate) {
            $birthDate = new DateTime($birthDate);
            $today = new DateTime('today');
            $age = $birthDate->diff($today)->y;
            return $age;
        }

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
        <div class="modal-header">
            <h5 class="modal-title text-danger" id="userModalLabel"><?php echo htmlspecialchars($usuario['nombreUsuario']); ?></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div class="text-center">
                <?php 
                $fotoPath = htmlspecialchars($usuario['foto']);
                if (!empty($usuario['foto']) && file_exists($fotoPath)): ?>
                    <img src="<?php echo $fotoPath; ?>" class="img-fluid rounded-circle mb-2" alt="<?php echo htmlspecialchars($usuario['nombreUsuario']); ?>" style="width: 150px; height: 150px;">
                <?php else: ?>
                    <img src="../assets/uploads/default-profile.png" class="img-fluid rounded-circle mb-2" alt="Perfil por defecto" style="width: 150px; height: 150px;">
                <?php endif; ?>
                <p class="text-muted"><?php echo getStarRating($usuario['ratingAverage']); ?></p>
                <p class="text-muted">Edad: <?php echo calculateAge($usuario['fechaNacimiento']); ?> años</p>
            </div>
            <p><strong>Descripción:</strong> <?php echo htmlspecialchars($usuario['descripcion']); ?></p>
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="text-end">
                    <a href="ver_resenas.php?id=<?php echo $userId; ?>" class="btn btn-danger">
                        <i class="fas fa-plus"></i> Ver reseñas
                    </a>
                </div>
            <?php else: ?>
                <div class="alert alert-warning text-center mt-3" role="alert">
                    Inicia sesión para ver las reseñas completas.
                </div>
            <?php endif; ?>
        </div>
        <?php
    } else {
        echo "<p class='text-danger'>No se encontraron detalles para este usuario.</p>";
    }
} else {
    echo "<p class='text-danger'>No se proporcionó un ID de usuario.</p>";
}
?>
