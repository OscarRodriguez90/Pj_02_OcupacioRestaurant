<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Adjust path if necessary, but since this is included from pages/admin_dashboard.php, CWD is pages/
// However, using __DIR__ is safer.
include __DIR__ . '/../database/conexion.php';

// Verificar permisos de acceso al Dashboard
if (!isset($_SESSION['rol'])) {
    header("Location: ../pages/login.php");
    exit();
}

$allowed_roles = ['admin', 'gerent', 'manteniment'];

if (!in_array($_SESSION['rol'], $allowed_roles)) {
    // Si es camarero, lo mandamos a selección de sala
    if ($_SESSION['rol'] === 'camarero') {
        header("Location: ../pages/selecciona_sala.php");
        exit();
    }
    // Si es otro rol no autorizado o desconocido
    $access_denied = true;
    $denied_message = "No tienes permisos para acceder a esta página (Dashboard).";
} else {
    $access_denied = false;
}

// Obtenir estadístiques si tiene permiso
if (!$access_denied) {
    try {
        $stmt = $conn->query("SELECT COUNT(*) FROM usuario");
        $total_usuarios = $stmt->fetchColumn();

        $stmt = $conn->query("SELECT COUNT(*) FROM usuario WHERE estado = 'activo'");
        $usuarios_activos = $stmt->fetchColumn();

        $stmt = $conn->query("SELECT COUNT(*) FROM usuario WHERE estado = 'inactivo'");
        $usuarios_inactivos = $stmt->fetchColumn();

        $stmt = $conn->query("SELECT rol, COUNT(*) as count FROM usuario GROUP BY rol");
        $usuarios_por_rol = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $conn->query("SELECT COUNT(*) FROM sala");
        $total_salas = $stmt->fetchColumn();

        $stmt = $conn->query("SELECT COUNT(*) FROM mesa");
        $total_mesas = $stmt->fetchColumn();

        $stmt = $conn->query("SELECT COUNT(*) FROM historico WHERE DATE(horaDesocupacion) = CURDATE()");
        $ocupaciones_hoy = $stmt->fetchColumn();
    } catch (Exception $e) {
        // Fallback info
        $total_usuarios = $usuarios_activos = $usuarios_inactivos = $total_salas = $total_mesas = $ocupaciones_hoy = 0;
        $usuarios_por_rol = [];
    }
} else {
    // Inicializar variables vacías para evitar errores en la vista antes del exit (aunque haremos exit en la vista)
    $total_usuarios = 0;
    $usuarios_activos = 0;
    $usuarios_inactivos = 0;
    $usuarios_por_rol = [];
    $total_salas = 0;
    $total_mesas = 0;
    $ocupaciones_hoy = 0;
}
?>
