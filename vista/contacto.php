  <?php include 'header.php'; ?>

<div class="container">
        <header class="jumbotron my-4">
            <h1 class="display-4">Sobre Nosotros</h1>
            <p class="lead">Descubre quiénes somos y nuestra misión.</p>
        </header>

        <div class="row">
            <div class="col-lg-6">
                <h2>Nuestra Historia</h2>
                <p>Somos una empresa comprometida con la moda sostenible y el intercambio de prendas entre personas. Desde nuestro inicio en 2010, hemos trabajado para crear una plataforma donde puedas comprar, vender y intercambiar ropa de forma fácil y segura.</p>
                <p>Con miles de usuarios en todo el mundo, estamos transformando la manera en que la gente compra y utiliza la moda.</p>
            </div>
            <div class="col-lg-6">
                <img src="nosotros.jpg" alt="Nosotros" class="img-fluid rounded">
            </div>
        </div>

        <hr class="my-4">

        <div class="row">
            <div class="col-md-4">
                <h2>Nuestro Equipo</h2>
                <p>Conoce a nuestro equipo de profesionales apasionados por la moda y el comercio justo. Estamos aquí para ayudarte en cada paso del proceso, desde la publicación de tus productos hasta la satisfacción del cliente final.</p>
            </div>
            <div class="col-md-4">
                <h2>Nuestra Misión</h2>
                <p>Nuestra misión es promover un estilo de vida sostenible a través del intercambio y reutilización de prendas. Creemos que cada prenda tiene una historia y un potencial para seguir siendo útil mucho más allá de su primer propietario.</p>
            </div>
            <div class="col-md-4">
                <h2>Nuestras Ubicaciones</h2>
                <p>Estamos presentes en múltiples ubicaciones alrededor del mundo, con oficinas centrales en París, Londres y Nueva York. Encuentra la oficina más cercana a ti y visítanos.</p>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 offset-md-3">
                <?php
                // Verificar si se ha enviado el formulario
                if ($_SERVER["REQUEST_METHOD"] == "POST") {
                    // Recibir los datos del formulario
                    $nombre = $_POST['nombre'];
                    $email = $_POST['email'];
                    $mensaje = $_POST['mensaje'];

                    // Configurar el correo electrónico
                    $destinatario = "tudirecciondecorreo@example.com"; // Cambiar por tu dirección de correo electrónico
                    $asunto = "Nuevo mensaje de contacto de Vinted";

                    // Construir el cuerpo del correo
                    $cuerpo = "Nombre: $nombre\n";
                    $cuerpo .= "Email: $email\n\n";
                    $cuerpo .= "Mensaje:\n$mensaje";

                    // Enviar el correo electrónico
                    if (mail($destinatario, $asunto, $cuerpo)) {
                        // Si el correo se envió correctamente, mostrar un mensaje de éxito
                        echo '<div class="alert alert-success" role="alert">Mensaje enviado correctamente. Nos pondremos en contacto contigo pronto.</div>';
                    } else {
                        // Si hubo un error al enviar el correo, mostrar un mensaje de error
                        echo '<div class="alert alert-danger" role="alert">Hubo un error al enviar el mensaje. Por favor, inténtalo de nuevo más tarde.</div>';
                    }
                }
                ?>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="form-group">
                        <label for="nombre">Nombre:</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="mensaje">Mensaje:</label>
                        <textarea class="form-control" id="mensaje" name="mensaje" rows="5" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Enviar Mensaje</button>
                </form>
            </div>
        </div>
    </div>
  <?php include 'footer.php'; ?>