<?php
// Funcions de validació simples

function validar_nombre($nombre) {
    if (empty($nombre)) return "El nombre es obligatorio.";
    if (strlen($nombre) > 60) return "El nombre no puede exceder 60 caracteres.";
    return null;
}

function validar_apellidos($apellidos) {
    if (empty($apellidos)) return "Los apellidos son obligatorios.";
    if (strlen($apellidos) > 80) return "Los apellidos no pueden exceder 80 caracteres.";
    return null;
}

function validar_nombreUsu($nombreUsu) {
    if (empty($nombreUsu)) return "El nombre de usuario es obligatorio.";
    if (strlen($nombreUsu) < 3) return "El nombre de usuario debe tener mínimo 3 caracteres.";
    if (strlen($nombreUsu) > 60) return "El nombre de usuario no puede exceder 60 caracteres.";
    if (!preg_match('/^[a-zA-Z0-9_-]+$/', $nombreUsu)) 
        return "El nombre de usuario solo puede contener letras, números, guión y guión bajo.";
    return null;
}

function validar_email($email) {
    if (empty($email)) return "El email es obligatorio.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return "El email no es válido.";
    if (strlen($email) > 60) return "El email no puede exceder 60 caracteres.";
    return null;
}

function validar_dni($dni) {
    if (empty($dni)) return "El DNI es obligatorio.";
    if (!preg_match('/^[0-9]{8}[A-Z]$|^[XYZ][0-9]{7}[A-Z]$/', strtoupper($dni))) 
        return "El DNI/NIF no tiene formato válido (ej: 12345678A).";
    return null;
}

function validar_telefono($telefono) {
    if (empty($telefono)) return "El teléfono es obligatorio.";
    if (!preg_match('/^[0-9]{9}$/', $telefono)) return "El teléfono debe tener 9 dígitos.";
    return null;
}

function validar_fecha($fecha) {
    if (empty($fecha)) return "La fecha de contratación es obligatoria.";
    $f = DateTime::createFromFormat('Y-m-d', $fecha);
    if (!$f) return "La fecha de contratación no es válida.";
    if ($f > new DateTime()) return "La fecha de contratación no puede ser futura.";
    return null;
}

function validar_rol($rol) {
    $roles_validos = ['admin', 'gerent', 'camarero', 'manteniment', 'caixa'];
    if (empty($rol) || !in_array($rol, $roles_validos)) return "El rol no es válido.";
    return null;
}

function validar_password($password) {
    if (empty($password)) return "La contraseña es obligatoria.";
    if (strlen($password) < 6) return "La contraseña debe tener mínimo 6 caracteres.";
    if (strlen($password) > 255) return "La contraseña es demasiado larga.";
    return null;
}

function validar_estado($estado) {
    $estados_validos = ['activo', 'inactivo'];
    if (!in_array($estado, $estados_validos)) return "El estado no es válido.";
    return null;
}

// Función para recopilar errores
function recopilar_errores($data, $es_creacion = true) {
    $errors = [];
    
    if ($error = validar_nombre($data['nombre'] ?? '')) $errors[] = $error;
    if ($error = validar_apellidos($data['apellidos'] ?? '')) $errors[] = $error;
    if ($error = validar_nombreUsu($data['nombreUsu'] ?? '')) $errors[] = $error;
    if ($error = validar_email($data['email'] ?? '')) $errors[] = $error;
    if ($error = validar_dni($data['dni'] ?? '')) $errors[] = $error;
    if ($error = validar_telefono($data['telefono'] ?? '')) $errors[] = $error;
    if ($error = validar_fecha($data['fechaContratacion'] ?? '')) $errors[] = $error;
    if ($error = validar_rol($data['rol'] ?? '')) $errors[] = $error;
    
    if ($es_creacion) {
        if ($error = validar_password($data['password'] ?? '')) $errors[] = $error;
        if (($data['password'] ?? '') !== ($data['password_confirm'] ?? '')) 
            $errors[] = "Las contraseñas no coinciden.";
    } else {
        if (!empty($data['password']) && strlen($data['password']) < 6) 
            $errors[] = "La contraseña debe tener mínimo 6 caracteres.";
        if (!empty($data['estado']) && ($error = validar_estado($data['estado']))) $errors[] = $error;
    }
    
    return $errors;
}

?>
