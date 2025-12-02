<?php
if (filter_has_var(INPUT_POST, 'login')) {

    session_start();
    include_once('../database/conexion.php'); // $conn es un objeto PDO

    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $_SESSION['errorLog'] = [];

   if(empty($username)) {
        $_SESSION['errorLog'][] = 'El campo de usuario está vacío';
    } elseif (strlen($username) < 3 || strlen($username) > 20) {
        $_SESSION['errorLog'][] = 'El campo de usuario debe tener entre 3 y 20 caracteres';
    } elseif(empty($password)) {
        $_SESSION['errorLog'][] = 'El campo de contraseña está vacío';
    } elseif(strlen($password) < 8){
        $_SESSION['errorLog'][] = 'El campo de contraseña debe tener al menos 8 caracteres';
    } elseif(!preg_match('/[A-Z]/', $password)){
        $_SESSION['errorLog'][] = 'El campo de contraseña debe tener al menos una letra mayúscula';
    } elseif(!preg_match('/[0-9]/', $password)){
        $_SESSION['errorLog'][] = 'El campo de contraseña debe tener al menos un número';
    }

    // Si hay errores, redirigir y salir
    if(!empty($_SESSION['errorLog'])) {
        header("Location: ../pages/login.php?ErrorLogin");
        exit();
    }
}

    $sql = "SELECT idCamarero, nombreUsu, password FROM camarero WHERE nombreUsu = :username";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        header("Location: ../pages/login.php?error=UsuarioInexistente");
        exit();
    }
    // Si las contraseñas están HASHHEADAS 
    if (password_verify($password, $user['password'])) {
        $_SESSION['idCamarero'] = $user['idCamarero'];
        $_SESSION['username'] = $user['nombreUsu'];
        header("Location: ../pages/selecciona_sala.php");
    }

?>