<?php
session_start();
include '../database/conexion.php';
include '../src/auth/validaciones.php';

// Verificar que l'usuari sigui admin
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
    $_SESSION['error_usuarios'] = "No tienes permisos para crear usuarios.";
    header("Location: ../pages/admin_usuarios.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = [
        'nombre' => trim($_POST['nombre'] ?? ''),
        'apellidos' => trim($_POST['apellidos'] ?? ''),
        'nombreUsu' => trim($_POST['nombreUsu'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'dni' => trim($_POST['dni'] ?? ''),
        'telefono' => trim($_POST['telefono'] ?? ''),
        'fechaContratacion' => $_POST['fechaContratacion'] ?? '',
        'rol' => $_POST['rol'] ?? '',
        'password' => $_POST['password'] ?? '',
        'password_confirm' => $_POST['password_confirm'] ?? ''
    ];

    $errors = recopilar_errores($data, true);

    // Verificar que el nombre de usuario no exista ya
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM usuario WHERE nombreUsu = ?");
        $stmt->execute([$data['nombreUsu']]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = "El nombre de usuario ya existe.";
        }
    }

    if (!empty($errors)) {
        $_SESSION['error_usuarios'] = implode("<br>", $errors);
        header("Location: ../pages/admin_usuarios.php?accion=crear");
        exit();
    }

    $password_hash = password_hash($data['password'], PASSWORD_BCRYPT);

    try {
        $stmt = $conn->prepare("
            INSERT INTO usuario (nombre, apellidos, nombreUsu, email, dni, telefono, fechaContratacion, rol, password, estado)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'activo')
        ");
        
        $stmt->execute([
            $data['nombre'],
            $data['apellidos'],
            $data['nombreUsu'],
            $data['email'],
            $data['dni'],
            $data['telefono'],
            $data['fechaContratacion'],
            $data['rol'],
            $password_hash
        ]);

        $_SESSION['success_usuarios'] = "Usuario creado exitosamente.";
        header("Location: ../pages/admin_usuarios.php");
        exit();

    } catch (PDOException $e) {
        $_SESSION['error_usuarios'] = "Error al crear el usuario: " . $e->getMessage();
        header("Location: ../pages/admin_usuarios.php?accion=crear");
        exit();
    }
} else {
    header("Location: ../pages/admin_usuarios.php");
    exit();
}
?>
