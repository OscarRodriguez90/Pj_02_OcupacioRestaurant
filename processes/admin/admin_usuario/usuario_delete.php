<?php
session_start();
include __DIR__ . '/../../../database/conexion.php';

// Verificar que l'usuari sigui admin o camarero
if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], ['admin', 'camarero'])) {
    $_SESSION['error_usuarios'] = "No tienes permisos para eliminar usuarios.";
    header("Location: ../../../pages/admin/admin_usuarios.php");
    exit();
}

$idUsuario = intval($_GET['id'] ?? 0);

if ($idUsuario <= 0) {
    $_SESSION['error_usuarios'] = "ID de usuario inválido.";
    header("Location: ../../../pages/admin/admin_usuarios.php");
    exit();
}

// No permitir que un admin se elimine a sí mismo
if (isset($_SESSION['idUsuario']) && $_SESSION['idUsuario'] == $idUsuario) {
    $_SESSION['error_usuarios'] = "No puedes eliminar tu propia cuenta.";
    header("Location: ../../../pages/admin/admin_usuarios.php");
    exit();
}

try {
    // Eliminar el usuario
    $stmt = $conn->prepare("DELETE FROM usuario WHERE idUsuario = ?");
    $stmt->execute([$idUsuario]);

    if ($stmt->rowCount() > 0) {
        $_SESSION['success_usuarios'] = "Usuario eliminado exitosamente.";
    } else {
        $_SESSION['error_usuarios'] = "Usuario no encontrado.";
    }

    header("Location: ../../../pages/admin/admin_usuarios.php");
    exit();

} catch (PDOException $e) {
    $_SESSION['error_usuarios'] = "Error al eliminar el usuario: " . $e->getMessage();
    header("Location: ../../../pages/admin/admin_usuarios.php");
    exit();
}
?>
