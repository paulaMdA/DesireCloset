<?php
session_start();

// Habilitar la visualización de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir el archivo de conexión
require_once '../config/conexion.php';

// La conexión ya ha sido establecida en conexion.php y almacenada en $conn
if ($conn) {
    // Obtener los 20 usuarios con mejores valoraciones
    $query = "SELECT u.idUsuario, u.nombreUsuario, u.foto, u.descripcion, u.fechaNacimiento, AVG(v.valoracion) as ratingAverage
              FROM usuarios u 
              JOIN usuarios_roles ur ON u.idUsuario = ur.idUsuario
              LEFT JOIN valoraciones v ON u.idUsuario = v.idValorado
              WHERE ur.idRol = 2 AND u.fechaBaja IS NULL
              GROUP BY u.idUsuario
              ORDER BY ratingAverage DESC
              LIMIT 20";
    $stmt = $conn->prepare($query);

    if ($stmt->execute()) {
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $usuarios = [];
        error_log("Error: No se pudo ejecutar la consulta.");
    }
} else {
    $usuarios = [];
    error_log("Error: No se pudo establecer la conexión con la base de datos.");
}

include '../includes/header.php';

function getStarRating($rating) {
    if ($rating === null) {
        return '<i class="far fa-star text-warning"></i><i class="far fa-star text-warning"></i><i class="far fa-star text-warning"></i><i class="far fa-star text-warning"></i><i class="far fa-star text-warning"></i>';
    }
    
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

<main>
    <!-- Carrusel -->
    <div id="carouselExampleCaptions" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
            <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="1" aria-label="Slide 2"></button>
            <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="2" aria-label="Slide 3"></button>
        </div>
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="../assets/img/carousel1.jpg" class="d-block w-100" alt="First Slide">
                <div class="carousel-caption d-none d-md-block">
                    <h1>¿Quieres COMPRAR BRAGUITAS USADAS?</h1>
                    <h4>Si quieres comprar braguitas usadas, este es el sitio donde puedes hacerlo con total discreción, mira la que más te guste y solicita su compra.</h4>
                </div>
            </div>
            <div class="carousel-item">
                <img src="../assets/img/carousel2.jpg" class="d-block w-100" alt="Second Slide">
                <div class="carousel-caption d-none d-md-block">
                    <h1>Descubre las Mejores Bragas Usadas de Segunda Mano</h1>
                    <h4>
                        Explora también nuestra sección de <strong>ropa interior usada</strong> donde encontrarás piezas únicas y sensuales que 
                        no encontrarás en ningún otro lugar. Y para aquellos con intereses más específicos, ofrecemos productos relacionados con la 
                        <strong>osmofilia</strong>, satisfaciendo tus deseos más íntimos de manera segura y confidencial.
                    </h4>
                </div>
            </div>
            <div class="carousel-item">
                <img src="../assets/img/carousel-8.jpg" class="d-block w-100" alt="Third Slide">
                <div class="carousel-caption d-none d-md-block">
                    <h1>Compra Bragas Usadas de Marca en DesireCloset</h1>
                    <h4>
                        Nuestro catálogo incluye una amplia selección de <strong>bragas usadas de calidad</strong>, 
                        cuidadosamente seleccionadas para garantizar la satisfacción de nuestros clientes más exigentes.
                    </h4>
                </div>
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Anterior</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Siguiente</span>
        </button>
    </div>

    <!-- Secciones de Contenido -->
    <div class="principal container mt-3">
        <h1 class="section-title titulo text-danger text-center ">Compra y vende artículos fetichistas</h1>
        
        <div class="row mt-4">
            <div class="col-md-6">
                <img src="../assets/img/sujetador1.jpg" class="img-fluid" alt="Imagen">
            </div>
            <div class="col-md-6">
                <h2 class="section-title text-danger">Descubre las Mejores Bragas Usadas de Segunda Mano</h2>
                <p>
                    Bienvenido a <strong>DesireCloset</strong>, tu destino número uno para encontrar 
                    <strong>bragas usadas</strong> de alta calidad y <strong>bragas de segunda mano</strong> a precios increíbles. 
                    Sabemos que buscas <strong>bragas usadas baratas</strong> pero sin comprometer la calidad, y estamos aquí para 
                    ofrecerte exactamente eso. Nuestro catálogo incluye una amplia selección de <strong>bragas usadas de calidad</strong>, 
                    cuidadosamente seleccionadas para garantizar la satisfacción de nuestros clientes más exigentes.
                </p>
                <p>
                    Explora también nuestra sección de <strong>ropa interior usada</strong> donde encontrarás piezas únicas y sensuales que 
                    no encontrarás en ningún otro lugar. Y para aquellos con intereses más específicos, ofrecemos productos relacionados con la 
                    <strong>osmofilia</strong>, satisfaciendo tus deseos más íntimos de manera segura y confidencial.
                </p>
                <p>
                    Visítanos hoy y descubre por qué somos la mejor opción para <strong>bragas usadas de calidad</strong> y 
                    <strong>bragas usadas baratas</strong>. En <strong>DesireCloset</strong>, tu satisfacción es nuestra prioridad.
                </p>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-6">
                <h2 class="section-title text-danger">Compra Bragas Usadas de Marca en DesireCloset</h2>
                <p>
                    Si estás buscando <strong>bragas usadas</strong> y <strong>ropa interior usada</strong> de alta calidad, has llegado al 
                    lugar correcto. En <strong>DesireCloset</strong>, nos especializamos en ofrecer <strong>bragas de segunda mano</strong> 
                    que combinan estilo y asequibilidad. Nuestra selección de <strong>bragas usadas baratas</strong> te permite encontrar lo que buscas 
                    sin romper tu presupuesto.
                </p>
                <p>
                    Nuestras <strong>bragas usadas de calidad</strong> ofrecen una variedad de estilos y tamaños para satisfacer todos los gustos y preferencias. Nuestra prioridad es tu satisfacción y comodidad, ofreciéndote siempre 
                    los mejores productos al mejor precio.
                </p>
                <p>
                    Entendemos que la privacidad es crucial cuando se trata de comprar <strong>ropa interior usada</strong>. Por eso, garantizamos 
                    transacciones seguras y discretas, protegiendo tu identidad en todo momento. Para aquellos interesados en la <strong>osmofilia</strong>, 
                    ofrecemos productos especiales que cumplen con los más altos estándares de exigencia.
                </p>
            </div>
            <div class="col-md-6">
                <img src="../assets/img/tio.jpg" class="img-fluid" alt="Imagen">
            </div>
        </div>

        <h2 class="section-title text-danger mt-5">Conecta con nuestros usuarios destacados</h2>
        <div class="row mt-3">
            <?php if (!empty($usuarios)): ?>
                <?php foreach ($usuarios as $usuario): ?>
                    <div class="col-md-3 text-center mb-4">
                        <a href="#" onclick="openUserModal(<?php echo $usuario['idUsuario']; ?>)">
                            <?php 
                                $fotoPath = htmlspecialchars($usuario['foto'] ?? '../assets/uploads/default-profile.png');
                                if (!empty($usuario['foto']) && file_exists($fotoPath)): ?>
                                <img src="<?php echo $fotoPath; ?>" class="img-fluid rounded-circle mb-2" alt="<?php echo htmlspecialchars($usuario['nombreUsuario'] ?? 'Usuario'); ?>" style="width: 150px; height: 150px;">
                            <?php else: ?>
                                <img src="../assets/uploads/default-profile.png" class="img-fluid rounded-circle mb-2" alt="Default Profile" style="width: 150px; height: 150px;">
                            <?php endif; ?>
                        </a>
                        <h5 class="text-danger"><?php echo htmlspecialchars($usuario['nombreUsuario'] ?? 'Usuario'); ?></h5>
                        <p class="text-muted"><?php echo getStarRating($usuario['ratingAverage'] ?? 0); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center">
                    <p class="text-danger">No se encontraron usuarios.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<!-- Modal Structure for Users -->
<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <!-- Content loaded dynamically -->
        </div>
    </div>
</div>

<script>
function openUserModal(userId) {
    // Fetch user details via AJAX
    fetch('detalles_urs.php?id=' + userId)
        .then(response => response.text())
        .then(html => {
            document.querySelector('#userModal .modal-content').innerHTML = html;
            new bootstrap.Modal(document.getElementById('userModal')).show();
        });
}
</script>

<?php include '../includes/footer.php'; ?>
