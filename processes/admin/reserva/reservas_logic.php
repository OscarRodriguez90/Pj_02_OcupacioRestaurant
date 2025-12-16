<?php
// Lógica de carga de datos para la página de reservas (salas, mesas, reservas con filtros)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include __DIR__ . '/../../../database/conexion.php';

// Permitir acceso a cualquier usuario logueado
if (!isset($_SESSION['rol'])) {
    header("Location: login.php");
    exit();
}

// Sincronizar estado de las mesas (actualizar ocupadas/libres según reservas activas)
require_once __DIR__ . '/../admin_mesa/sync_mesas.php';
syncMesasStatus($conn);

// Cargar todas las salas disponibles
try {
    $stmt = $conn->query("SELECT idSala, nombre FROM sala ORDER BY nombre");
    $salas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $salas = [];
}

// Sala seleccionada
$selectedSala = isset($_GET['idSala']) ? intval($_GET['idSala']) 
                                      : (count($salas) ? intval($salas[0]['idSala']) : 0);

// Cargar mesas de la sala seleccionada (con filtro opcional de sillas mínimas)
$mesas = [];
if ($selectedSala) {
    $selectedSillas = isset($_GET['sillas']) ? intval($_GET['sillas']) : 0;
    if ($selectedSillas > 0) {
        $stmt = $conn->prepare("SELECT idMesa, nombre, estado, numSillas FROM mesa WHERE idSala = ? AND numSillas >= ? ORDER BY nombre");
        $stmt->execute([$selectedSala, $selectedSillas]);
    } else {
        $stmt = $conn->prepare("SELECT idMesa, nombre, estado, numSillas FROM mesa WHERE idSala = ? ORDER BY nombre");
        $stmt->execute([$selectedSala]);
    }
    $mesas = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fecha seleccionada
$selectedDate = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');

// Hora seleccionada (NUEVO FILTRO puntual) y franja horaria (rango)
$selectedHora   = $_GET['hora']   ?? "";
$selectedFranja = $_GET['franja'] ?? "";

// Cargar reservas de la sala y fecha seleccionadas (aplicando filtros)
$reservas = [];
if ($selectedSala) {
    try {

        // Construir consulta SQL con filtros dinámicos (fecha, hora, franja)
        $sql = "
            SELECT r.*, 
                   m.nombre AS mesaNombre, 
                   u.nombre AS usuarioNombre, u.apellidos AS usuarioApellidos, 
                   c.nombre AS camNombre, c.apellidos AS camApellidos
            FROM reserva r
            LEFT JOIN mesa m      ON r.idMesa = m.idMesa
            LEFT JOIN usuario u   ON r.idUsuario = u.idUsuario
            LEFT JOIN camarero c  ON r.idCamarero = c.idCamarero
            WHERE r.idSala = :idSala
              AND r.fecha  = :fecha
        ";

        $params = [
            ':idSala' => $selectedSala,
            ':fecha'  => $selectedDate
        ];

        // Filtrar por hora puntual (reservas que estén activas a esa hora)
        if (!empty($selectedHora)) {
            $sql .= " AND :hora BETWEEN r.horaInicio AND r.horaFin";
            $params[':hora'] = $selectedHora;
        }

        // Filtrar por franja horaria (reservas que solapen con el rango)
        if (!empty($selectedFranja)) {
            // Formato esperado: HH:MM-HH:MM
            $parts = explode('-', $selectedFranja);
            if (count($parts) === 2) {
                $sql .= " AND NOT (r.horaFin <= :fInicio OR r.horaInicio >= :fFin)";
                $params[':fInicio'] = $parts[0];
                $params[':fFin']    = $parts[1];
            }
        }

        $sql .= " ORDER BY r.horaInicio";

        $stmtR = $conn->prepare($sql);
        $stmtR->execute($params);
        $reservas = $stmtR->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        $reservas = [];
        $errorMsg = 'Error cargando reservas: ' . $e->getMessage();
    }
}

// Recuperar mensajes de sesión (error/éxito)
$errorMsg    = $_SESSION['error']   ?? ($errorMsg ?? '');
$successMsg  = $_SESSION['success'] ?? '';
unset($_SESSION['error'], $_SESSION['success']);
?>
