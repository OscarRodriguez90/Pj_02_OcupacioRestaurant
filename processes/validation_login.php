<?php
if (filter_has_var(INPUT_POST, 'login')) {

    session_start();
    include_once('../database/conexion.php'); // $conn es un objeto PDO

    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $_SESSION['errorLog'] = [];

   if(empty($username)) {
        $_SESSION['errorLog'][] = 'El campo de usuario está vacío';
    } elseif (strlen($username) < 3 || strlen($username) > 60) {
        $_SESSION['errorLog'][] = 'El campo de usuario debe tener entre 3 y 60 caracteres';
    } elseif(empty($password)) {
        $_SESSION['errorLog'][] = 'El campo de contraseña está vacío';
    } elseif(strlen($password) < 6){
        $_SESSION['errorLog'][] = 'El campo de contraseña debe tener al menos 6 caracteres';
    }

    // Si hay errores, redirigir y salir
    if(!empty($_SESSION['errorLog'])) {
        header("Location: ../pages/login.php?ErrorLogin");
        exit();
    }

    // Intentar acceder con la tabla usuario (NUEVO SISTEMA)
    $sql = "SELECT idUsuario, nombreUsu, password, rol, estado FROM usuario WHERE nombreUsu = :username AND estado = 'activo'";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['idUsuario'] = $user['idUsuario'];
        $_SESSION['username'] = $user['nombreUsu'];
        $_SESSION['rol'] = $user['rol'];
        
        // Redirigir según el rol
        if ($user['rol'] === 'admin') {
            header("Location: ../pages/admin/admin_mesas.php");
        } else {
            header("Location: ../pages/selecciona_sala.php");
        }
        exit();
    }

    // Si falla, intentar con la tabla camarero (COMPATIBILIDAD CON SISTEMA ANTERIOR)
    $sql = "SELECT idCamarero, nombreUsu, password FROM camarero WHERE nombreUsu = :username";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['idCamarero'] = $user['idCamarero'];
        $_SESSION['username'] = $user['nombreUsu'];
        $_SESSION['rol'] = 'camarero'; // Rol por defecto para antiguos usuários
        header("Location: ../pages/selecciona_sala.php");
        exit();
    }

    // Si no encuentra en ninguna tabla
    header("Location: ../pages/login.php?error=UsuarioInexistente");
    exit();
}
?>
