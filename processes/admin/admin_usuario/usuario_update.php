<?php
session_start();
include __DIR__ . '/../../../database/conexion.php';
include __DIR__ . '/../../../src/auth/validaciones.php';

// Verificar que l'usuari sigui admin o camarero
if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], ['admin', 'camarero'])) {
    $_SESSION['error_usuarios'] = "No tienes permisos para actualizar usuarios.";
    header("Location: ../../../pages/admin/admin_usuarios.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = [
        'idUsuario' => intval($_POST['idUsuario'] ?? 0),
        'nombre' => trim($_POST['nombre'] ?? ''),
        'apellidos' => trim($_POST['apellidos'] ?? ''),
        'nombreUsu' => trim($_POST['nombreUsu'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'dni' => trim($_POST['dni'] ?? ''),
        'telefono' => trim($_POST['telefono'] ?? ''),
        'fechaContratacion' => $_POST['fechaContratacion'] ?? '',
        'rol' => $_POST['rol'] ?? '',
        'estado' => $_POST['estado'] ?? 'activo',
        'password' => $_POST['password'] ?? ''
    ];

    $errors = recopilar_errores($data, false);

    if ($data['idUsuario'] <= 0) $errors[] = "ID de usuario inválido.";

    // Verificar que el nombre de usuario no exista ya (excepto para el usuario actual)
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM usuario WHERE nombreUsu = ? AND idUsuario != ?");
        $stmt->execute([$data['nombreUsu'], $data['idUsuario']]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = "El nombre de usuario ya existe.";
        }
    }

    if (!empty($errors)) {
        $_SESSION['error_usuarios'] = implode("<br>", $errors);
        header("Location: ../../../pages/admin/admin_usuarios.php?accion=editar&id=" . $data['idUsuario']);
        exit();
    }

    try {
        if (!empty($data['password'])) {
            $password_hash = password_hash($data['password'], PASSWORD_BCRYPT);
            $stmt = $conn->prepare("
                UPDATE usuario 
                SET nombre = ?, apellidos = ?, nombreUsu = ?, email = ?, dni = ?, telefono = ?, fechaContratacion = ?, rol = ?, estado = ?, password = ?
                WHERE idUsuario = ?
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
                $data['estado'],
                $password_hash,
                $data['idUsuario']
            ]);
        } else {
            $stmt = $conn->prepare("
                UPDATE usuario 
                SET nombre = ?, apellidos = ?, nombreUsu = ?, email = ?, dni = ?, telefono = ?, fechaContratacion = ?, rol = ?, estado = ?
                WHERE idUsuario = ?
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
                $data['estado'],
                $data['idUsuario']
            ]);
        }

        $_SESSION['success_usuarios'] = "Usuario actualizado exitosamente.";
        header("Location: ../../../pages/admin/admin_usuarios.php");
        exit();

    } catch (PDOException $e) {
        $_SESSION['error_usuarios'] = "Error al actualizar el usuario: " . $e->getMessage();
        header("Location: ../../../pages/admin/admin_usuarios.php?accion=editar&id=" . $data['idUsuario']);
        exit();
    }
} else {
    header("Location: ../../../pages/admin/admin_usuarios.php");
    exit();
}
?>
