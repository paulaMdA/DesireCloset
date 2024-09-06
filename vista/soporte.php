   <?php include '../includes/header.php'; ?>

    <div class="container mt-5">
        <section id="soporte" class="mb-5">
            <h2 class="mb-4 text-center text-danger">Soporte</h2>
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title text-center">Formulario de Soporte</h5>
                            <form id="soporteForm" method="GET" action="mailto:soporte@DesireCloset.com" enctype="text/plain">
                                <div class="form-group">
                                    <label for="motivo">Motivo del Contacto</label>
                                    <select class="form-control" id="motivo" name="subject" required>
                                        <option value="Consulta general">Consulta general</option>
                                        <option value="Problemas con la cuenta">Problemas con la cuenta</option>
                                        <option value="Reporte de un usuario">Reporte de un usuario</option>
                                        <option value="Problemas con una transacción">Problemas con una transacción</option>
                                        <option value="Otro">Otro</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="mensaje">Mensaje</label>
                                    <textarea class="form-control" id="mensaje" name="body" rows="5" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary btn-block">Enviar</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <?php include '../includes/footer.php'; ?>

