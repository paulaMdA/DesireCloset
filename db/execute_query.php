<?php
    // Detalles de la conexión a la base de datos
    $host = 'db5015909185.hosting-data.io';
    $username = 'dbu3832332';
    $password = 'Temporal1_2024!';
    $database = 'dbs12966751';

    // Crear una conexión a la base de datos
    $conn = new mysqli($host, $username, $password, $database);

    // Verificar la conexión
    if ($conn->connect_error) {
        die("Error de conexión: " . $conn->connect_error);
    }

    // Obtener la consulta del formulario
    $query = $_POST['query'];

    // Ejecutar la consulta
    $result = $conn->query($query);

    // Mostrar el resultado
    if ($result) {
        echo "<h2>Resultado de la Consulta:</h2>";
        echo "<table border='1'>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            foreach ($row as $value) {
                echo "<td>" . $value . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "Error al ejecutar la consulta: " . $conn->error;
    }

    // Cerrar la conexión
    $conn->close();
?>
