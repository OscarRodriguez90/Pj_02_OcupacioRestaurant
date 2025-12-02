<?php
session_start();
require_once '../database/conexion.php';

// Validaciones básicas
if (filter_has_var(INPUT_POST, 'Registrar')) {

    // Obtener y limpiar los datos del formulario
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $dni = trim($_POST['dni'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $fecha = $_POST['fecha'] ?? '';
    $password = $_POST['password'] ?? '';  
    
    $_SESSION['error'] = [];

    // Validar nombre
    if (empty($nombre)) {
        $_SESSION['error'][] = "El nombre es obligatorio<br>";
    } else if(strlen($nombre) < 3) {
        $_SESSION['error'][] = "El nombre debe tener al menos 3 caracteres<br>";
    } else if(strlen($nombre) > 20) {
        $_SESSION['error'][] = "El nombre debe tener menos de 20 caracteres<br>";
    }

    // Validar apellido
    $separacion = preg_split('/\s+/', $apellido);
    $separacion = array_filter($separacion); // Eliminar elementos vacíos
    
    if (empty($apellido)) {
        $_SESSION['error'][] = "El apellido es obligatorio<br>";
    } else if(count($separacion) < 2) {
        $_SESSION['error'][] = "El apellido debe tener al menos 2 palabras<br>";
    } else if(strlen($apellido) < 3) {
        $_SESSION['error'][] = "El apellido debe tener al menos 3 caracteres<br>";
    } else if(strlen($apellido) > 20) {
        $_SESSION['error'][] = "El apellido debe tener menos de 20 caracteres<br>";
    }

    // Validar username
    if (empty($username)) {
        $_SESSION['error'][] = "El nombre de usuario es obligatorio<br>";
    } else if(strlen($username) < 3) {
        $_SESSION['error'][] = "El nombre de usuario debe tener al menos 3 caracteres<br>";
    } else if(strlen($username) > 20) {
        $_SESSION['error'][] = "El nombre de usuario debe tener menos de 20 caracteres<br>";
    }

    // Validacion avanzada y compleja DNI

    if(empty($dni)){
        $_SESSION['error'][] = "El DNI es obligatorio<br>";
    } else if(!preg_match('/^[a-zA-Z0-9]+$/', $dni)) {
        $_SESSION['error'][] = "El DNI debe contener solo letras y números<br>";
    } else {
        $letra = substr($dni, -1);
        $numeros = substr($dni, 0, -1);
        if (!is_numeric($numeros)) {
            $_SESSION['error'][] = "El DNI debe tener números seguidos de una letra<br>";
        } else {
            $letrasValidas = "TRWAGMYFPDXBNJZSQVHLCKE";
            $calculoLetra = $letrasValidas[$numeros % 23];
            if (strtoupper($letra) !== $calculoLetra) {
                $_SESSION['error'][] = "La letra del DNI no es correcta<br>";
            }
        }
    }
    


   

    // Validar telefono - CORREGIDO: is_nan no existe en PHP
    if (empty($telefono)) {
        $_SESSION['error'][] = "El teléfono es obligatorio<br>";
    } else if(strlen($telefono) < 9 || strlen($telefono) > 10) {
        $_SESSION['error'][] = "El teléfono debe tener entre 9 y 10 caracteres<br>";
    } else if(!ctype_digit($telefono)) { // CORRECCIÓN: usar ctype_digit en lugar de is_nan
        $_SESSION['error'][] = "El teléfono debe ser numérico<br>";
    }

    // Validar correo
    if (empty($correo)) {
        $_SESSION['error'][] = "El correo es obligatorio<br>";
    } else if(!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'][] = "El correo debe ser válido<br>";
    }

    // Validar contraseña
    if (empty($password)) {
        $_SESSION['error'][] = "La contraseña es obligatoria<br>";
    } else if(!preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $_SESSION['error'][] = "La contraseña debe tener al menos una letra mayúscula y un número<br>";
    }

    // Si hay errores, redirigir y salir
    if(!empty($_SESSION['error'])) {
        header("Location: ../pages/insertar_camarero.php?ErrorAlCrearRegistro");
        exit();
    }

    // SI NO HAY ERRORES, proceder con la inserción
    try {
        // Verificar si el nombre de usuario ya existe
        $sql = "SELECT idCamarero FROM camarero WHERE nombreUsu = :username OR email = :correo OR dni = :dni";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':username' => $username,
            ':correo' => $correo,
            ':dni' => $dni
        ]);
        
        $usuarioExistente = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($usuarioExistente) {
            $_SESSION['error'][] = "Error: El nombre de usuario, correo o DNI ya están registrados";
            header("Location: ../pages/insertar_camarero.php?ErrorAlCrearRegistro");
            exit();
        }

        // Hashear la contraseña
        $contrasenaHash = password_hash($password, PASSWORD_DEFAULT);

        // Insertar el nuevo camarero
        $sql = "INSERT INTO camarero (nombre, apellidos, nombreUsu, dni, telefono, email, fechaContratacion, password) 
                VALUES (:nombre, :apellidos, :username, :dni, :telefono, :email, :fecha, :password)";
        
        $stmt = $conn->prepare($sql);
        
        $resultado = $stmt->execute([
            ':nombre' => $nombre,
            ':apellidos' => $apellido,
            ':username' => $username,
            ':dni' => $dni,
            ':telefono' => $telefono,
            ':email' => $correo,
            ':fecha' => $fecha,
            ':password' => $contrasenaHash 
            
        ]);

        if ($resultado) {
            // Éxito - redirigir a la página de selección de sala
            header("Location: ../pages/Camareros.php?registro=exitoso");
            exit();
        } else {
            $_SESSION['error'][] = "Error al crear el registro en la base de datos";
            header("Location: ../pages/insertar_camarero.php?ErrorAlCrearRegistro");
            exit();
        }

    } catch (PDOException $e) {
        $_SESSION['error'][] = "Error de base de datos: " . $e->getMessage();
        header("Location: ../pages/insertar_camarero.php?ErrorAlCrearRegistro");
        exit();
    } catch (Exception $e) {
        $_SESSION['error'][] = "Error: " . $e->getMessage();
        header("Location: ../pages/insertar_camarero.php?ErrorAlCrearRegistro");
        exit();
    }
}
?>