<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin = $isLoggedIn && isset($_SESSION['role']) && $_SESSION['role'] == 'admin';

// Obtener el nombre del archivo actual
$current_file = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DesireCloset</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/css/bootstrap.min.css">
    <link href="../assets/css/style.css" rel="stylesheet">
    <!-- jQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg" style="background-color: #f8f9fa;">
            <div class="container-fluid">
                <a class="navbar-brand" href="#">
                    <img src="../assets/img/logo.jpg" alt="Logo de DesireCloset" class="logo" style="width:100px;">
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item"><a class="nav-link fs-5 mx-2 <?php echo $current_file == 'principal.php' ? 'active' : ''; ?>" aria-current="page" href="../vista/principal.php" style="color: black; font-weight: bold;">Home</a></li>
                        <li class="nav-item"><a class="nav-link fs-5 mx-2 <?php echo $current_file == 'todos.php' ? 'active' : ''; ?>" href="../vista/todos.php" style="color: black; font-weight: bold;">Todas las Categorías</a></li>
                        <li class="nav-item"><a class="nav-link fs-5 mx-2 <?php echo $current_file == 'braga.php' ? 'active' : ''; ?>" href="../vista/braga.php" style="color: black; font-weight: bold;">Braga y Tanga</a></li>
                        <li class="nav-item"><a class="nav-link fs-5 mx-2 <?php echo $current_file == 'sujetadores.php' ? 'active' : ''; ?>" href="../vista/sujetadores.php" style="color: black; font-weight: bold;">Sujetadores</a></li>
                        <li class="nav-item"><a class="nav-link fs-5 mx-2 <?php echo $current_file == 'fotosdepie.php' ? 'active' : ''; ?>" href="../vista/fotosdepie.php" style="color: black; font-weight: bold;">Fotos de pies</a></li>
                        <li class="nav-item"><a class="nav-link fs-5 mx-2 <?php echo $current_file == 'juguetessexuales.php' ? 'active' : ''; ?>" href="../vista/juguetessexuales.php" style="color: black; font-weight: bold;">Juguetes Sexuales</a></li>
                        <?php if ($isAdmin): ?>
                            <li class="nav-item"><a class="nav-link fs-5 mx-2 <?php echo $current_file == 'admin.php' ? 'active' : ''; ?>" href="admin.php" style="color: black; font-weight: bold;">Panel de Admin</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
