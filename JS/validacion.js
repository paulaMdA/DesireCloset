'use strict';

// Función para validar formularios con Bootstrap
function initializeFormValidation() {
    var forms = document.getElementsByClassName('needs-validation');
    Array.prototype.filter.call(forms, function(form) {
        form.addEventListener('submit', function(event) {
            if (form.checkValidity() === false) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
}

// Función para previsualizar la imagen de perfil
function previewImage(event) {
    var reader = new FileReader();
    reader.onload = function() {
        var output = document.getElementById('fotoPreview');
        output.src = reader.result;
        output.style.display = 'block';
    }
    reader.readAsDataURL(event.target.files[0]);
}
//Funcion para editar el perfil 
  (function() {
        'use strict';
        window.addEventListener('load', function() {
            var forms = document.getElementsByClassName('needs-validation');
            Array.prototype.filter.call(forms, function(form) {
                form.addEventListener('submit', function(event) {
                    if (form.checkValidity() === false) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        }, false);
    })();
// Función para manejar la vista previa de las fotos del producto
function handleFileSelect(evt) {
    var preview = document.getElementById('preview');
    preview.innerHTML = '';
    var files = evt.target.files;
    if (files.length > 3) {
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Puedes subir un máximo de 3 fotos.'
        });
        evt.target.value = ''; // Limpiar el campo de archivo
        return;
    }
    for (var i = 0; i < files.length; i++) {
        var file = files[i];
        var reader = new FileReader();
        reader.onload = (function(theFile) {
            return function(e) {
                var img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'img-thumbnail';
                img.style.maxWidth = '150px';
                img.style.marginRight = '10px';
                preview.appendChild(img);
            };
        })(file);
        reader.readAsDataURL(file);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    var forms = document.getElementsByClassName('needs-validation');
    Array.prototype.filter.call(forms, function(form) {
        form.addEventListener('submit', function(event) {
            if (form.checkValidity() === false) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });

    var fotoInputs = document.querySelectorAll('#foto1, #foto2, #foto3');
    fotoInputs.forEach(function(input) {
        input.addEventListener('change', handleFileSelect, false);
    });
});

// Función para manejar el borrado de productos
window.borrarProducto = function(idProducto) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "¡No podrás revertir esto!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, bórralo!'
    }).then((result) => {
        if (result.isConfirmed) {
            var form = document.createElement('form');
            form.method = 'post';
            form.action = '';
            
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'idProducto';
            input.value = idProducto;
            
            var action = document.createElement('input');
            action.type = 'hidden';
            action.name = 'action';
            action.value = 'borrar';
            
            form.appendChild(input);
            form.appendChild(action);
            document.body.appendChild(form);
            form.submit();
        }
    });
}



// JavaScript para la validación del formulario y previsualización de imágenes
function initializeFormValidation() {
    'use strict';
    var forms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
}

// Función para previsualizar las fotos del producto
function previewFotos(event) {
    var preview = document.getElementById('preview');
    preview.innerHTML = '';

    if (event.target.files) {
        Array.from(event.target.files).forEach(function (file) {
            var reader = new FileReader();
            reader.onload = function (e) {
                var img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'img-thumbnail';
                img.style = 'width: 100px; height: 100px; margin-right: 10px;';
                preview.appendChild(img);
            };
            reader.readAsDataURL(file);
        });
    }
}

document.addEventListener('DOMContentLoaded', function () {
    initializeFormValidation();

    var fotosInput = document.getElementById('fotos');
    if (fotosInput) {
        fotosInput.addEventListener('change', previewFotos);
    }
});

     // Función para mostrar un mensaje de éxito con SweetAlert Editar_producto
        function mostrarMensajeExito(mensaje) {
            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: mensaje
            });
        }

        // Función para mostrar un mensaje de error con SweetAlert
        function mostrarMensajeError(mensaje) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: mensaje
            });
        }
 
     



