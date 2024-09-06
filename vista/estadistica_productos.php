<?php
require_once '../config/conexion.php';

$database = new Database();
$conn = $database->getConnection();

try {
    // Verificar si la columna fechaRegistro existe en la tabla productos
    $columnCheck = $conn->query("SHOW COLUMNS FROM productos LIKE 'fechaRegistro'");
    $columnExists = $columnCheck->rowCount() > 0;

    if (!$columnExists) {
        // Agregar la columna fechaRegistro a la tabla productos si no existe
        $conn->exec("ALTER TABLE productos ADD fechaRegistro TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
    }

    // Obtener productos por estado y mes usando la fecha de registro de productos
    $query = "
        SELECT 
            MONTH(fechaRegistro) as mes, 
            estado, 
            COUNT(*) as total 
        FROM transacciones
        JOIN productos ON transacciones.idProducto = productos.idProducto
        GROUP BY MONTH(fechaRegistro), estado";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $productosMensuales = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Preparar datos para el gráfico
    $totalProductos = array_fill(0, 12, 0);
    $vendidosPorMes = array_fill(0, 12, 0);
    $reservadosPorMes = array_fill(0, 12, 0);

    foreach ($productosMensuales as $producto) {
        $mes = $producto['mes'] - 1; // Ajustar el índice del mes (0-11)
        $totalProductos[$mes] += $producto['total']; // Sumar al total de productos
        if ($producto['estado'] == 'vendido') {
            $vendidosPorMes[$mes] += $producto['total'];
        } elseif ($producto['estado'] == 'reservado') {
            $reservadosPorMes[$mes] += $producto['total'];
        }
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

include '../includes/header_admin.php';
?>


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
    <!-- /#sidebar-wrapper -->

    <!-- Page Content -->
    <div id="page-content-wrapper">
        <div class="container mt-5">
            <h2>Estadísticas de Productos</h2>
            <div class="row mt-4">
                <div class="col-12">
                    <canvas id="productosMensualesChart" style="width:100%; height:500px;"></canvas>
                </div>
            </div>
        </div>
    </div>
    <!-- /#page-content-wrapper -->
</div>
<!-- /#wrapper -->


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Chart.js para productos mensuales
    const ctx = document.getElementById('productosMensualesChart').getContext('2d');
    const productosMensualesChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
            datasets: [
                {
                    label: 'Total Productos',
                    data: <?php echo json_encode($totalProductos); ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Productos Vendidos',
                    data: <?php echo json_encode($vendidosPorMes); ?>,
                    backgroundColor: 'rgba(220, 53, 69, 0.5)',
                    borderColor: 'rgba(220, 53, 69, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Productos Reservados',
                    data: <?php echo json_encode($reservadosPorMes); ?>,
                    backgroundColor: 'rgba(255, 206, 86, 0.5)',
                    borderColor: 'rgba(255, 206, 86, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>

<?php include '../includes/footer_admin.php'; ?>
