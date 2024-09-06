<?php
// Iniciar la sesión para poder utilizar las variables de sesión
session_start();

// Incluir el archivo de conexión a la base de datos
require_once '../config/conexion.php';

// Verificar si el usuario ha iniciado sesión y si es un administrador
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    // Si no es administrador, redirigir al login
    header('Location: login.php');
    exit();
}

// Crear una instancia de la clase Database y obtener la conexión
$database = new Database();
$conn = $database->getConnection();

// Inicializar las variables para almacenar los datos
$totalUsuarios = 0;
$totalProductosEnVenta = 0;
$totalProductosVendidos = 0;
$totalIngresos = 0;
$ingresosMensuales = array_fill(0, 12, 0); // Array para los ingresos mensuales

// Intentar obtener los datos de la base de datos
try {
    // Consulta para obtener el total de usuarios no administradores
    $stmt = $conn->query("
        SELECT COUNT(*) 
        FROM usuarios u
        JOIN usuarios_roles ur ON u.idusuario = ur.idusuario
        WHERE ur.idrol = 2
    ");
    $totalUsuarios = $stmt->fetchColumn(); // Guardar el resultado en la variable

    // Consulta para obtener el total de productos en venta
    $stmt = $conn->query("
        SELECT COUNT(*) 
        FROM productos p 
        LEFT JOIN transacciones t ON p.idProducto = t.idProducto 
        WHERE t.estado IS NULL OR t.estado = 'enventa'
    ");
    $totalProductosEnVenta = $stmt->fetchColumn(); // Guardar el resultado en la variable

    // Consulta para obtener el total de productos vendidos
    $stmt = $conn->query("SELECT COUNT(*) FROM transacciones WHERE estado = 'vendido'");
    $totalProductosVendidos = $stmt->fetchColumn(); // Guardar el resultado en la variable

    // Consulta para calcular los ingresos totales (106 es el ingreso por usuario)
    $stmt = $conn->query("
        SELECT COUNT(*) * 106 AS totalIngresos
        FROM usuarios u
        JOIN usuarios_roles ur ON u.idusuario = ur.idusuario
        WHERE ur.idrol != 1
    ");
    $totalIngresos = $stmt->fetchColumn(); // Guardar el resultado en la variable

    // Consulta para obtener el número de usuarios suscritos por mes con el rol de usuario
    $stmt = $conn->query("
        SELECT 
            MONTH(u.fecharegistro) as mes,
            COUNT(*) as suscritos
        FROM usuarios u
        JOIN usuarios_roles ur ON u.idusuario = ur.idusuario
        WHERE ur.idrol = 2
        GROUP BY MONTH(u.fecharegistro)
    ");
    $suscripcionesMensuales = $stmt->fetchAll(PDO::FETCH_ASSOC); // Guardar los resultados en un array

    // Calcular ingresos mensuales basados en los usuarios suscritos
    foreach ($suscripcionesMensuales as $suscripcion) {
        $mes = $suscripcion['mes'] - 1; // Ajustar el mes para que sea de 0 a 11
        $ingresosMensuales[$mes] = $suscripcion['suscritos'] * (106 / 12); // Ingresos mensuales por suscripción
    }

} catch (PDOException $e) {
    // Mostrar mensaje de error en caso de fallo
    echo "Error: " . $e->getMessage();
}

// Incluir el encabezado del administrador
include '../includes/header_admin.php';
?>

<!-- Contenido de la página -->
<div class="admin d-flex" id="wrapper" style="min-height: 100vh; overflow-x: hidden;">
    <!-- Sidebar -->
    <div class="bg-dark border-right" id="sidebar-wrapper" style="width: 150px;">
        <div class="sidebar-heading text-white">DesireCloset Admin</div>
        <div class="list-group list-group-flush">
            <a href="admin.php" class="list-group-item list-group-item-action bg-dark text-white">Dashboard</a>
            <a href="ver_usuarios.php" class="list-group-item list-group-item-action bg-dark text-white">Usuarios</a>
            <a href="ver_productos.php" class="list-group-item list-group-item-action bg-dark text-white">Productos</a>
            <a href="estadistica_productos.php" class="list-group-item list-group-item-action bg-dark text-white">Estadística</a>
            <a href="verificar_dni.php" class="list-group-item list-group-item-action bg-dark text-white">Verificar DNI</a>
          
            <a href="logout.php" class="list-group-item list-group-item-action bg-dark text-white">Cerrar Sesión</a>
        </div>
    </div>

    <!-- Contenido principal -->
    <main id="page-content-wrapper" class="flex-grow-1 d-flex flex-column" style="min-width: 0;">
        <div class="container mt-5">
            <h2>Dashboard</h2>
            <div class="row">
                <div class="col-lg-3 col-md-6 col-sm-12 mb-4">
                    <div class="card text-white bg-dark h-100">
                        <div class="card-header bg-danger text-white">Usuarios</div>
                        <div class="card-body">
                            <h5 class="card-title text-white"><?php echo $totalUsuarios; ?></h5>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-12 mb-4">
                    <div class="card text-white bg-dark h-100">
                        <div class="card-header bg-danger text-white">Productos en Venta</div>
                        <div class="card-body">
                            <h5 class="card-title text-white"><?php echo $totalProductosEnVenta; ?></h5>
                            <p class="card-text text-white">Total de productos en venta.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-12 mb-4">
                    <div class="card text-white bg-dark h-100">
                        <div class="card-header bg-danger text-white">Productos Vendidos</div>
                        <div class="card-body">
                            <h5 class="card-title text-white"><?php echo $totalProductosVendidos; ?></h5>
                            <p class="card-text text-white">Total de productos vendidos.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-12 mb-4">
                    <div class="card text-white bg-dark h-100">
                        <div class="card-header bg-danger text-white">Ingresos Totales</div>
                        <div class="card-body">
                            <h5 class="card-title text-white">€<?php echo number_format($totalIngresos, 2); ?></h5>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-12">
                    <canvas id="ingresosMensualesChart" style="width:100%; height:500px;"></canvas>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
    $(document).ready(function () {
        // Chart.js para ingresos mensuales
        const ctx = document.getElementById('ingresosMensualesChart').getContext('2d');
        const ingresosMensualesChart = new Chart(ctx, {
            type: 'bar', // Tipo de gráfico: barra
            data: {
                labels: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'], // Etiquetas del eje X
                datasets: [{
                    label: 'Ingresos Mensuales (€)', // Etiqueta del conjunto de datos
                    data: <?php echo json_encode($ingresosMensuales); ?>, // Datos del conjunto de datos, convertidos de PHP a JSON
                    backgroundColor: 'rgba(220, 53, 69, 0.5)', // Color de fondo de las barras (con transparencia)
                    borderColor: 'rgba(220, 53, 69, 1)', // Color del borde de las barras
                    borderWidth: 1 // Ancho del borde de las barras
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true // El eje Y comienza en cero
                    }
                }
            }
        });
    });
</script>

<?php include '../includes/footer_admin.php'; ?>
