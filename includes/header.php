<?php
// Iniciar la sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario está autenticado
$isLoggedIn = isset($_SESSION['user_id']);

// Verificar si el usuario es administrador
$isAdmin = $isLoggedIn && isset($_SESSION['role']) && $_SESSION['role'] == 'admin';

// Obtener el nombre del archivo actual para activar el enlace correspondiente en la barra de navegación
$current_file = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DesireCloset</title>
    <link rel="shortcut icon" href="../assets/img/logo.jpg" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
   

    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <header class="py-3" style="background-color: #000000;">
        <div class="header container">
            <div class="row align-items-center justify-content-between">
                <!-- Logo y Título del sitio -->
                <div class="col-lg-8 d-flex align-items-center">
                    <a href="#" class="navbar-brand">
                        <img src="../assets/img/logo.jpg" alt="Logo de DesireCloset" class="logo" style="width:100px;">
                    </a>
                    <div class="ms-2">
                        <h2 class="text-danger display-6 mb-0">DesireCloset</h2>
                        <h5 class="text-danger text-center display-10 mb-0">Conectando Fantasías</h5>
                    </div>
                </div>
                <!-- Barra de búsqueda y enlaces de usuario -->
                <div class="col-lg-4 d-flex align-items-center justify-content-end">
                    <!-- Barra de búsqueda -->
                    <form class="d-flex me-3" action="../vista/busqueda.php" method="GET">
                        <input class="form-control me-2" type="search" name="busqueda" placeholder="Buscar" aria-label="Buscar">
                        <button type="submit" class="btn btn-danger"><i class="fas fa-search"></i></button>
                    </form>
                    <!-- Enlaces de usuario -->
                    <ul class="navbar-nav d-flex flex-row">
                        <li class="nav-item"><a class="nav-link text-danger me-3" href="../vista/miperfil.php"><i class="fas fa-user fa-lg"></i></a></li>
                        <?php if ($isLoggedIn): ?>
                            <!-- Menú desplegable para usuarios autenticados -->
                            <li class="nav-item dropdown">
                                <a class="nav-link text-danger dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-cog fa-lg"></i>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <li><a class="dropdown-item" href="editar_perfil.php"><i class="fas fa-pencil-alt"></i> Editar perfil</a></li>
                                    <li>
                                        <form action="borrar_perfil.php" method="post">
                                            <input type="hidden" name="confirm_delete" value="yes">
                                            <button type="submit" class="dropdown-item"><i class="fas fa-trash-alt"></i> Borrar perfil</button>
                                        </form>
                                    </li>
                                    <li><a class="dropdown-item" href="verInformacion.php"><i class="fas fa-info-circle"></i> Ver información</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="../vista/logout.php" method="post" class="dropdown-item p-0">
                                            <button type="submit" class="btn btn-link text-danger p-0 m-0"><i class="fas fa-sign-out-alt fa-lg"></i> Cerrar sesión</button>
                                        </form>
                                    </li>
                                </ul>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </header>
    <!-- Barra de navegación principal -->
    <nav class="navbar navbar-expand-lg navbar-light bg-danger">
        <div class="container">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarCollapse">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a href="../vista/principal.php" class="nav-link fs-4 <?= $current_file == 'principal.php' ? 'active' : '' ?>">Home</a></li>
                    <li class="nav-item"><a href="../vista/todos.php" class="nav-link fs-4 <?= $current_file == 'todos.php' ? 'active' : '' ?>">Todo</a></li>
                    <li class="nav-item"><a href="../vista/braga.php" class="nav-link fs-4 <?= $current_file == 'braga.php' ? 'active' : '' ?>">Braga y Tanga</a></li>
                    <li class="nav-item"><a href="../vista/sujetadores.php" class="nav-link fs-4 <?= $current_file == 'sujetadores.php' ? 'active' : '' ?>">Sujetadores</a></li>
                    <li class="nav-item"><a href="../vista/fotosdepie.php" class="nav-link fs-4 <?= $current_file == 'fotosdepie.php' ? 'active' : '' ?>">Fotos de pies</a></li>
                    <li class="nav-item"><a href="../vista/juguetessexuales.php" class="nav-link fs-4 <?= $current_file == 'juguetessexuales.php' ? 'active' : '' ?>">Juguetes Sexuales</a></li>
                    <?php if ($isAdmin): ?>
                        <li class="nav-item"><a href="admin.php" class="nav-link fs-4 <?= $current_file == 'admin.php' ? 'active' : '' ?>">Panel de Admin</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
