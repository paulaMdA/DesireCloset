<!DOCTYPE html>
<html>
<head>
    <title>Consola de sql</title>
</head>
<body>
    <h1>Escribe la query que quieres lanzar: select,insert,delete...</h1>
    <form method="post">
        <input type="text" name="query" size=100 placeholder="Introduce tu consulta SQL">
        <input type="submit" name="submit" value="Lanzar">
    </form>

    <?php
    // Verifica si el formulario ha sido enviado
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Obtiene la consulta desde el formulario
        $query = $_POST["query"];

        // Conecta a la base de datos MySQL
        $db_host = "db5015909185.hosting-data.io";
        $db_user = "dbu3832332";
        $db_password = "Temporal1_2024!";
        $db_name = "dbs12966751";

        $conn = new mysqli($db_host, $db_user, $db_password, $db_name);

        // Ejecuta la consulta
        $result = $conn->query($query);

        // Muestra el resultado
        if ($result) {
            echo "<h2>Resultado de la consulta:</h2>";
            echo "<table>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                foreach ($row as $value) {
                    echo "<td>$value</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "Error ejecutando la consulta: " . $conn->error;
        }

        // Cierra la conexión a la base de datos
        $conn->close();
    }
    ?>
</body>
</html>
