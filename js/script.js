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

    sweetalertEstadoMesa();

    sweetalertCerrarSesion();
}

function validarUsuario(){
    var username = document.getElementById('username').value;
    var usernameError = document.getElementById('usernameError');

    if(username=== ''){
        usernameError.textContent = 'El nombre de usuario es obligatorio';
    }else if(username.length < 3){
        usernameError.textContent = 'El nombre de usuario debe tener al menos 3 caracteres';
    }else if(username.length > 20){
       usernameError.textContent = 'El nombre de usuario debe tener menos de 20 caracteres';
    }else{
        usernameError.textContent = '';
    }
}

function validarPassword(){
    var password = document.getElementById('password').value;
    var passwordError = document.getElementById('passwordError');

    if(password=== ''){
        passwordError.textContent = 'La contraseña es obligatoria';
    }else if(!/[A-Z]/.test(password) || !/[0-9]/.test(password)){
        passwordError.textContent = 'La contraseña debe contener al menos una mayúscula y un número';
    }else{
        passwordError.textContent = '';
        
    }
}
// Validar confirmar contraseña
function validarConfirmarPassword(){
    var password = document.getElementById('password').value;
    var confirmPassword = document.getElementById('confirm_password').value;
    var confirmPasswordError = document.getElementById('confirmPasswordError');
    if(confirmPassword === ''){
        confirmPasswordError.textContent = 'Por favor, confirma tu contraseña';
    }
    else if(password !== confirmPassword){
        confirmPasswordError.textContent = 'Las contraseñas no coinciden';
    }else{
        confirmPasswordError.textContent = '';
    }
}

function validarNombre(){
    var nombre = document.getElementById('nombre').value;
    var nombreError = document.getElementById('nombreError');

    if(nombre.length < 3){
        nombreError.textContent = 'El nombre debe tener al menos 3 caracteres';
    }else if(nombre.length > 20){
       nombreError.textContent = 'El nombre debe tener menos de 20 caracteres';
    }else if(!/^[a-zA-Z]*$/.test(nombre)){
        nombreError.textContent = 'El nombre solo puede contener letras';
    }else{
        nombreError.textContent = '';
    }
}

function validarApellidos(){
    var apellidos = document.getElementById('apellido').value.trim();
    var apellidosError = document.getElementById('apellidoError');

     // Eliminar espacios múltiples y dividir en palabras
    var separacion= apellidos.split(/\s+/).filter(palabra => palabra.length > 0);
    if(apellidos=== ''){
        apellidosError.textContent = 'Los apellidos son obligatorios';
    }else if(separacion.length < 2){
        apellidosError.textContent = 'Los apellidos deben estar compuestos por al menos 2 palabras';
    }else if(apellidos.length > 30){
        apellidosError.textContent = 'Los apellidos deben tener menos de 30 caracteres';
    }else if(!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]*$/.test(apellidos)){
        apellidosError.textContent = 'Los apellidos solo pueden contener letras y espacios';
    }else{
        apellidosError.textContent = '';
    }
}

function validarDNI(){
    
    var dni = document.getElementById('dni').value.toUppercase();
    var dniError = document.getElementById('dniError');
    var letrasDNI = 'TRWAGMYFPDXBNJZSQVHLCKE';
    if(dni === ''){
        dniError.textContent = 'El DNI es obligatorio';
    }else if(!/^[0-9]{8}[A-Z]$/.test(dni)){
        dniError.textContent = 'El formato del DNI es incorrecto';
    }else{
        var numero = parseInt(dni.substring(0, 8), 10);
        var letra = dni.charAt(8);
        var letraCorrecta = letrasDNI.charAt(numero % 23);
        if(letra !== letraCorrecta){
            dniError.textContent = 'La letra del DNI es incorrecta';
        }else{
            dniError.textContent = '';
        }
    }

}


function validarTelefono(){
    var telefono = document.getElementById('telefono').value;
    var telefonoError = document.getElementById('telefonoError');

    if(telefono=== ''){
        telefonoError.textContent= 'El teléfono es obligatorio';
    }else if(telefono.length < 9 || telefono.length > 10){
        telefonoError.textContent= 'El teléfono debe tener 9 caracteres';
    }else if(!/^[0-9]*$/.test(telefono)){
        telefonoError.textContent= 'El teléfono debe contener solo números';
    }else{
        telefonoError.textContent= '';
    }
}
function validarCorreo(){
    var correo = document.getElementById('correo').value;
    var correoError = document.getElementById('correoError');

    if(correo === ''){

        correoError.innerHTML = 'El correo es obligatorio';

    } else if (!/^[a-zA-Z0-9._%+-]+@gmail\.(com|es)$/.test(correo)){
      
        correoError.innerHTML = 'Debe ser un correo válido';
    
    } else {
        
        correoError.innerHTML = '';

    }
}

function validarFecha(){

    var fecha = document.getElementById('fecha').value;
    var fechaError = document.getElementById('fechaError');

    if (fecha === ''){

        fechaError.innerHTML = 'La fecha es obligatoria';

    } else {

        var fechaIngresada = new Date(fecha);
        var hoy = new Date();

        if (fechaIngresada > hoy){
        
            fechaError.innerHTML = 'La fecha no puede ser futura';
        
        } else {
            
            fechaError.innerHTML = '';
        
        }
    }
}



// |||||||||||||||||||||| SWEETALERT2 ||||||||||||||||||||||

// SweetAlert2: pregunta y muestra éxito tras cambiar estado de mesa
// document.addEventListener('DOMContentLoaded', function() {
function sweetalertEstadoMesa() {
    var form = document.getElementById('form-cambiar-estado');
    if (form) {
        form.onsubmit = function(evento) {
            evento.preventDefault(); // Previene que se envíe el formulario
                    if (window.Swal) {
                    Swal.fire({
                        text: '¿Cambiar estado?',
                        icon: 'question',
                        showCancelButton: true,
                        cancelButtonText: 'Cancelar'
                    }).then(function(result) {
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
        form.onsubmit = function(evento) {
            evento.preventDefault(); // Previene que se envíe el formulario
                    if (window.Swal) {
                    Swal.fire({
                        text: '¿Seguro que quieres cerrar sesión?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, cerrar sesión',
                        cancelButtonText: 'Cancelar'
                    }).then(function(result) {
                        if (result.isConfirmed) form.submit();
                    });
                } else {
                    form.submit();
                }
        };
    }
}