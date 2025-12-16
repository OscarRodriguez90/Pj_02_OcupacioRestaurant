window.onload = () => {


    nombre = document.getElementById("nombre");
    if (nombre) nombre.onblur = validarNombre;

    apellidos = document.getElementById("apellido");
    if (apellidos) apellidos.onblur = validarApellidos;

    username = document.getElementById("username");
    if (username) username.onblur = validarUsuario;

    dni = document.getElementById("dni");
    if (dni) dni.onblur = validarDNI;

    telefono = document.getElementById("telefono");
    if (telefono) telefono.onblur = validarTelefono;

    correo = document.getElementById("correo");
    if (correo) correo.onblur = validarCorreo;

    fecha = document.getElementById("fecha");
    if (fecha) fecha.onblur = validarFecha;

    pwd = document.getElementById("password");
    if (pwd) pwd.onblur = validarPassword;

    confirmar = document.getElementById("confirm_password");
    if (confirmar) confirmar.onblur = validarConfirmarPassword;

    // onblur attachments
    salaNombre = document.getElementById("nombreSala");
    if (salaNombre) salaNombre.onblur = validarNombreSala;

    fondoSala = document.getElementById("fondoSala");
    if (fondoSala) fondoSala.onchange = validarFondoSala;

    mesaNombre = document.getElementById("nombreMesa");
    if (mesaNombre) mesaNombre.onblur = validarNombreMesa;

    mesaSillas = document.getElementById("numSillas");
    if (mesaSillas) mesaSillas.onblur = validarNumSillas;

    // onsubmit attachments to block invalid data
    var formSala = document.querySelector('form[action*="sala_create.php"]');
    if (formSala) {
        formSala.onsubmit = function (e) {
            validarNombreSala();
            validarFondoSala();
            var err1 = document.getElementById('nombreSalaError');
            var err2 = document.getElementById('fondoSalaError');
            if ((err1 && err1.textContent !== '') || (err2 && err2.textContent !== '')) e.preventDefault();
        };
    }

    var formMesa = document.querySelector('form[action*="mesa_create.php"]');
    if (formMesa) {
        formMesa.onsubmit = function (e) {
            validarNombreMesa();
            validarNumSillas();
            var err1 = document.getElementById('nombreMesaError');
            var err2 = document.getElementById('numSillasError');
            if ((err1 && err1.textContent !== '') || (err2 && err2.textContent !== '')) e.preventDefault();
        };
    }

    var formCrearUsuario = document.getElementById('form-crear-usuario');
    if (formCrearUsuario) {
        formCrearUsuario.onsubmit = function (e) {
            validarNombre();
            validarApellidos();
            validarUsuario();
            validarCorreo();
            validarDNI();
            validarTelefono();
            validarFecha();
            validarPassword();
            validarConfirmarPassword();

            // Check specific errors
            var ids = ['nombreError', 'apellidoError', 'usernameError', 'correoError', 'dniError', 'telefonoError', 'fechaError', 'passwordError', 'confirmPasswordError'];
            var hasError = false;
            ids.forEach(function (id) {
                var el = document.getElementById(id);
                if (el && el.textContent !== '') hasError = true;
            });
            if (hasError) e.preventDefault();
        };
    }

    var formEditarUsuario = document.getElementById('form-editar-usuario');
    if (formEditarUsuario) {
        formEditarUsuario.onsubmit = function (e) {
            validarNombre();
            validarApellidos();
            validarUsuario();
            validarCorreo();
            validarDNI();
            validarTelefono();
            validarFecha();
            validarPassword(); // Optional check handled inside function

            var ids = ['nombreError', 'apellidoError', 'usernameError', 'correoError', 'dniError', 'telefonoError', 'fechaError', 'passwordError'];
            var hasError = false;
            ids.forEach(function (id) {
                var el = document.getElementById(id);
                if (el && el.textContent !== '') hasError = true;
            });
            if (hasError) e.preventDefault();
        };
    }

    sweetalertEstadoMesa();

    sweetalertCerrarSesion();
}

function validarUsuario() {
    var username = document.getElementById('username').value;
    var usernameError = document.getElementById('usernameError');

    if (username === '') {
        usernameError.textContent = 'El nombre de usuario es obligatorio';
    } else if (username.length < 3) {
        usernameError.textContent = 'El nombre de usuario debe tener al menos 3 caracteres';
    } else if (username.length > 20) {
        usernameError.textContent = 'El nombre de usuario debe tener menos de 20 caracteres';
    } else {
        usernameError.textContent = '';
    }
}

function validarPassword() {
    var passwordInput = document.getElementById('password');
    if (!passwordInput) return;

    var password = passwordInput.value;
    var passwordError = document.getElementById('passwordError');
    var isCreation = document.getElementById('form-crear-usuario') !== null;

    if (password === '') {
        if (isCreation) {
            passwordError.textContent = 'La contraseña es obligatoria';
        } else {
            passwordError.textContent = '';
        }
    } else if (!/[A-Z]/.test(password) || !/[0-9]/.test(password)) {
        passwordError.textContent = 'La contraseña debe contener al menos una mayúscula y un número';
    } else if (isCreation && password.length < 6) {
        passwordError.textContent = 'La contraseña debe tener al menos 6 caracteres';
    } else if (password.length > 0 && password.length < 6) {
        passwordError.textContent = 'La contraseña debe tener al menos 6 caracteres';
    } else {
        passwordError.textContent = '';

    }
}
// Validar confirmar contraseña
function validarConfirmarPassword() {
    var passwordInput = document.getElementById('password');
    var confirmInput = document.getElementById('confirm_password');
    var confirmPasswordError = document.getElementById('confirmPasswordError');

    if (!confirmInput || !confirmPasswordError) return;

    var password = passwordInput ? passwordInput.value : '';
    var confirmPassword = confirmInput.value;

    if (confirmPassword === '') {
        confirmPasswordError.textContent = 'Por favor, confirma tu contraseña';
    }
    else if (password !== confirmPassword) {
        confirmPasswordError.textContent = 'Las contraseñas no coinciden';
    } else {
        confirmPasswordError.textContent = '';
    }
}

function validarNombre() {
    var nombre = document.getElementById('nombre').value;
    var nombreError = document.getElementById('nombreError');

    if (nombre.length < 3) {
        nombreError.textContent = 'El nombre debe tener al menos 3 caracteres';
    } else if (nombre.length > 20) {
        nombreError.textContent = 'El nombre debe tener menos de 20 caracteres';
    } else if (!/^[a-zA-Z]*$/.test(nombre)) {
        nombreError.textContent = 'El nombre solo puede contener letras';
    } else {
        nombreError.textContent = '';
    }
}

function validarApellidos() {
    var apellidos = document.getElementById('apellido').value.trim();
    var apellidosError = document.getElementById('apellidoError');

    // Eliminar espacios múltiples y dividir en palabras
    var separacion = apellidos.split(/\s+/).filter(palabra => palabra.length > 0);
    if (apellidos === '') {
        apellidosError.textContent = 'Los apellidos son obligatorios';
    } else if (separacion.length < 2) {
        apellidosError.textContent = 'Los apellidos deben estar compuestos por al menos 2 palabras';
    } else if (apellidos.length > 30) {
        apellidosError.textContent = 'Los apellidos deben tener menos de 30 caracteres';
    } else if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]*$/.test(apellidos)) {
        apellidosError.textContent = 'Los apellidos solo pueden contener letras y espacios';
    } else {
        apellidosError.textContent = '';
    }
}

function validarDNI() {

    var dni = document.getElementById('dni').value.toUppercase();
    var dniError = document.getElementById('dniError');
    var letrasDNI = 'TRWAGMYFPDXBNJZSQVHLCKE';
    if (dni === '') {
        dniError.textContent = 'El DNI es obligatorio';
    } else if (!/^[0-9]{8}[A-Z]$/.test(dni)) {
        dniError.textContent = 'El formato del DNI es incorrecto';
    } else {
        var numero = parseInt(dni.substring(0, 8), 10);
        var letra = dni.charAt(8);
        var letraCorrecta = letrasDNI.charAt(numero % 23);
        if (letra !== letraCorrecta) {
            dniError.textContent = 'La letra del DNI es incorrecta';
        } else {
            dniError.textContent = '';
        }
    }

}


function validarTelefono() {
    var telefono = document.getElementById('telefono').value;
    var telefonoError = document.getElementById('telefonoError');

    if (telefono === '') {
        telefonoError.textContent = 'El teléfono es obligatorio';
    } else if (telefono.length < 9 || telefono.length > 10) {
        telefonoError.textContent = 'El teléfono debe tener 9 caracteres';
    } else if (!/^[0-9]*$/.test(telefono)) {
        telefonoError.textContent = 'El teléfono debe contener solo números';
    } else {
        telefonoError.textContent = '';
    }
}
function validarCorreo() {
    var correo = document.getElementById('correo').value;
    var correoError = document.getElementById('correoError');

    if (correo === '') {

        correoError.innerHTML = 'El correo es obligatorio';

    } else if (!/^[a-zA-Z0-9._%+-]+@gmail\.(com|es)$/.test(correo)) {

        correoError.innerHTML = 'Debe ser un correo válido';

    } else {

        correoError.innerHTML = '';

    }
}

function validarFecha() {

    var fecha = document.getElementById('fecha').value;
    var fechaError = document.getElementById('fechaError');

    if (fecha === '') {

        fechaError.innerHTML = 'La fecha es obligatoria';

    } else {

        var fechaIngresada = new Date(fecha);
        var hoy = new Date();

        if (fechaIngresada > hoy) {

            fechaError.innerHTML = 'La fecha no puede ser futura';

        } else {

            fechaError.innerHTML = '';

        }
    }
}

function validarNombreSala() {
    var input = document.getElementById('nombreSala');
    var error = document.getElementById('nombreSalaError');
    if (!input || !error) return;

    var nombre = input.value.trim();
    if (nombre === '') {
        error.textContent = 'El nombre de la sala es obligatorio';
    } else if (nombre.length > 50) {
        error.textContent = 'El nombre no puede superar los 50 caracteres';
    } else {
        error.textContent = '';
    }
}

function validarFondoSala() {
    var input = document.getElementById('fondoSala');
    var error = document.getElementById('fondoSalaError');
    if (!input || !error) return;

    if (input.files && input.files.length > 0) {
        var file = input.files[0];
        var allowed = ['image/jpeg', 'image/png', 'image/gif'];
        if (!allowed.includes(file.type)) {
            error.textContent = 'El archivo debe ser una imagen (JPG, PNG, GIF)';
        } else {
            error.textContent = '';
        }
    } else {
        error.textContent = '';
    }
}

function validarNombreMesa() {
    var input = document.getElementById('nombreMesa');
    var error = document.getElementById('nombreMesaError');
    if (!input || !error) return;

    var nombre = input.value.trim();
    if (nombre === '') {
        error.textContent = 'El nombre de la mesa es obligatorio';
    } else if (nombre.length > 50) {
        error.textContent = 'El nombre no puede superar los 50 caracteres';
    } else {
        error.textContent = '';
    }
}

function validarNumSillas() {
    var input = document.getElementById('numSillas');
    var error = document.getElementById('numSillasError');
    if (!input || !error) return;

    var sillas = input.value;
    if (sillas === '' || parseInt(sillas) < 1) {
        error.textContent = 'Debe haber al menos 1 silla';
    } else {
        error.textContent = '';
    }
}



// |||||||||||||||||||||| SWEETALERT2 ||||||||||||||||||||||

// SweetAlert2: pregunta y muestra éxito tras cambiar estado de mesa
// document.addEventListener('DOMContentLoaded', function() {
function sweetalertEstadoMesa() {
    var form = document.getElementById('form-cambiar-estado');
    if (form) {
        form.onsubmit = function (evento) {
            evento.preventDefault(); // Previene que se envíe el formulario
            if (window.Swal) {
                Swal.fire({
                    text: '¿Cambiar estado?',
                    icon: 'question',
                    showCancelButton: true,
                    cancelButtonText: 'Cancelar'
                }).then(function (result) {
                    if (result.isConfirmed) form.submit();
                });
            } else {
                form.submit();
            }
        };
    }
}

function sweetalertCerrarSesion() {
    var form = document.getElementById('cerrar-sesion');
    if (form) {
        form.onsubmit = function (evento) {
            evento.preventDefault(); // Previene que se envíe el formulario
            if (window.Swal) {
                Swal.fire({
                    text: '¿Seguro que quieres cerrar sesión?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, cerrar sesión',
                    cancelButtonText: 'Cancelar'
                }).then(function (result) {
                    if (result.isConfirmed) form.submit();
                });
            } else {
                form.submit();
            }
        };
    }
}

// Admin page handlers: attach events without inline JS
window.onload = (function (orig) {
    return function () {
        if (typeof orig === 'function') orig();

        // Sala selector (admin_mesas)
        var salaSelect = document.getElementById('sala-select');
        if (salaSelect) salaSelect.onchange = function () { window.location.href = '?idSala=' + this.value; };

        // Delete links with confirmation (no native confirm/alert)
        var deletes = document.querySelectorAll('.js-delete');
        deletes.forEach(function (a) {
            a.onclick = function (e) {
                e.preventDefault();
                var href = a.getAttribute('href');
                var msg = a.dataset.confirm || '¿Eliminar?';
                if (window.Swal) {
                    Swal.fire({ text: msg, icon: 'question', showCancelButton: true, cancelButtonText: 'Cancelar' }).then(function (result) { if (result.isConfirmed) window.location.href = href; });
                    return false;
                }
                // Fallback: inline confirm UI (non-blocking, no alert/confirm)
                if (a._confirmShown) return false;
                a._confirmShown = true;
                var wrapper = document.createElement('span');
                wrapper.style.marginLeft = '8px';
                var yes = document.createElement('button');
                yes.textContent = 'Sí';
                yes.className = 'btn-danger';
                yes.style.marginRight = '4px';
                var no = document.createElement('button');
                no.textContent = 'No';
                no.className = 'btn-ghost';
                wrapper.appendChild(yes);
                wrapper.appendChild(no);
                a.parentNode.insertBefore(wrapper, a.nextSibling);
                yes.onclick = function () { window.location.href = href; };
                no.onclick = function () { wrapper.remove(); a._confirmShown = false; };
                return false;
            };
        });

        // Reservas page handlers (no inline JS, no alerts)
        var salaSelect = document.getElementById('idSala');
        if (salaSelect) salaSelect.onchange = function () { window.location.href = 'reservas.php?idSala=' + encodeURIComponent(this.value); };

        // Handle mesa selection from table
        var reservarBtns = document.querySelectorAll('.btn-reservar-mesa');
        reservarBtns.forEach(function (btn) {
            btn.onclick = function () {
                var mesaId = this.getAttribute('data-idmesa');
                var selectMesa = document.getElementById('idMesa');
                if (selectMesa) {
                    selectMesa.value = mesaId;
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            };
        });

        function updateHoras() {
            var franjaEl = document.getElementById('franja');
            if (!franjaEl) return;
            var franja = franjaEl.value;
            var hi = document.getElementById('horaInicio');
            var hf = document.getElementById('horaFin');
            if (franja) {
                var parts = franja.split('-');
                if (hi) hi.value = parts[0] || '';
                if (hf) hf.value = parts[1] || '';
            } else {
                if (hi) hi.value = '';
                if (hf) hf.value = '';
            }
        }

        function isPastDate(dateStr) {
            if (!dateStr) return false;
            var today = new Date();
            var d = new Date(dateStr + 'T00:00:00');
            var todayZero = new Date(today.toDateString());
            return d < todayZero;
        }

        function showInlineError(elId, msg) {
            var el = document.getElementById(elId);
            if (!el) {
                console.warn('Missing error container:', elId, msg);
                return;
            }
            el.textContent = msg;
            el.style.display = msg ? 'block' : 'none';
            if (msg) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        var franjaSelect = document.getElementById('franja');
        if (franjaSelect) franjaSelect.onchange = updateHoras;

        var reservaForm = document.querySelector('form[action="../processes/reserva_create.php"]');
        if (reservaForm) {
            reservaForm.onsubmit = function (e) {
                showInlineError('reservaErrors', '');
                var idSala = document.getElementById('idSala') ? document.getElementById('idSala').value : '';
                var idMesa = document.getElementById('idMesa') ? document.getElementById('idMesa').value : '';
                var franja = document.getElementById('franja') ? document.getElementById('franja').value : '';
                var fecha = document.getElementById('fecha') ? document.getElementById('fecha').value : '';

                if (!idSala) { e.preventDefault(); showInlineError('reservaErrors', 'Selecciona la sala.'); return false; }
                if (!idMesa) { e.preventDefault(); showInlineError('reservaErrors', 'Selecciona la mesa.'); return false; }
                if (!fecha) { e.preventDefault(); showInlineError('reservaErrors', 'Selecciona la fecha de la reserva.'); return false; }
                if (isPastDate(fecha)) { e.preventDefault(); showInlineError('reservaErrors', 'No puedes reservar en una fecha pasada.'); return false; }
                if (!franja) { e.preventDefault(); showInlineError('reservaErrors', 'Selecciona una franja horaria.'); return false; }

                updateHoras();
                var hi = document.getElementById('horaInicio').value;
                var hf = document.getElementById('horaFin').value;
                if (!hi || !hf) { e.preventDefault(); showInlineError('reservaErrors', 'Franja inválida, por favor selecciona otra.'); return false; }
                // allow submit
            };
        }

        var buscarForm = document.getElementById('form-buscar-fecha');
        if (buscarForm) {
            buscarForm.onsubmit = function (e) {
                showInlineError('buscarErrors', '');
                var fechaBuscar = document.getElementById('fecha_buscar').value;
                var todayStr = new Date().toISOString().slice(0, 10);
                if (!fechaBuscar) { e.preventDefault(); showInlineError('buscarErrors', 'Selecciona la fecha a buscar.'); return false; }
                if (fechaBuscar < todayStr) { e.preventDefault(); showInlineError('buscarErrors', 'No puedes buscar reservas en fechas pasadas. Se mostrará la fecha actual.'); document.getElementById('fecha_buscar').value = todayStr; window.location.href = 'reservas.php?idSala=' + encodeURIComponent(document.querySelector('input[name="idSala"]').value) + '&fecha=' + encodeURIComponent(todayStr); return false; }
            };
        }

        // Validación de filtros en sala.php (Fecha y Hora)
        var filtroFecha = document.getElementById('filtro_fecha');
        var filtroHora = document.getElementById('filtro_hora');

        function validarFiltrosSala() {
            if (!filtroFecha || !filtroHora) return;

            var fechaVal = filtroFecha.value;
            var horaVal = filtroHora.value; // "HH:00"

            if (!fechaVal) return;

            var now = new Date();
            var selectedDate = new Date(fechaVal + 'T00:00:00');
            var today = new Date();
            today.setHours(0, 0, 0, 0);

            // Validar Fecha
            if (selectedDate < today) {
                if (window.Swal) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Fecha inválida',
                        text: 'No puedes seleccionar una fecha pasada.'
                    });
                } else {
                    alert('No puedes seleccionar una fecha pasada.');
                }
                // Reset a hoy
                var y = now.getFullYear();
                var m = String(now.getMonth() + 1).padStart(2, '0');
                var d = String(now.getDate()).padStart(2, '0');
                filtroFecha.value = y + '-' + m + '-' + d;
                // Re-trigger validation to check hour
                validarFiltrosSala();
                return;
            }

            // Validar Hora si la fecha es hoy
            if (selectedDate.getTime() === today.getTime()) {
                var currentHour = now.getHours();
                var selectedHour = parseInt(horaVal.split(':')[0], 10);

                // Si la franja termina antes de ahora (o igual), es pasada.
                // Franjas son de 2h. Ej: 12:00-14:00. Si son las 14:01, es pasado.
                if ((selectedHour + 2) <= currentHour) {
                    if (window.Swal) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Hora inválida',
                            text: 'No puedes seleccionar una hora pasada.'
                        });
                    } else {
                        alert('No puedes seleccionar una hora pasada.');
                    }

                    // Reset hora to current slot
                    var h = currentHour;
                    if (h % 2 !== 0) h--;
                    var s = String(h).padStart(2, '0') + ':00';
                    filtroHora.value = s;
                }
            }
        }

        if (filtroFecha) filtroFecha.onchange = validarFiltrosSala;
        if (filtroHora) filtroHora.onchange = validarFiltrosSala;

        checkServerMessages();
    };

    function checkServerMessages() {
        if (!window.Swal) return;

        // 1. Generic Error (server-alert-msg) - e.g. Validation errors
        var errorInput = document.getElementById('server-alert-msg');
        if (errorInput && errorInput.value) {
            Swal.fire({
                icon: 'error',
                title: 'Atención',
                text: errorInput.value
            });
        }

        // 2. Access Denied (access-denied-msg) - Redirects back
        var deniedInput = document.getElementById('access-denied-msg');
        if (deniedInput && deniedInput.value) {
            Swal.fire({
                icon: 'error',
                title: 'Acceso Denegado',
                text: deniedInput.value,
                confirmButtonText: 'Volver',
                allowOutsideClick: false,
                allowEscapeKey: false
            }).then(function (result) {
                if (result.isConfirmed) {
                    if (document.referrer && document.referrer.indexOf(window.location.host) !== -1) {
                        window.history.back();
                    } else {
                        window.location.href = '../pages/login.php'; // Fallback
                    }
                }
            });
        }
    }

})(window.onload);