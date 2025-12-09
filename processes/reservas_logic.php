<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include __DIR__ . '/../database/conexion.php';

// Allow any logged-in user
if (!isset($_SESSION['rol'])) {
    header("Location: login.php");
    exit();
}

// Sync mesas status
require_once __DIR__ . '/sync_mesas.php';
syncMesasStatus($conn);

// Load salas
try {
    $stmt = $conn->query("SELECT idSala, nombre FROM sala ORDER BY nombre");
    $salas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $salas = [];
}

// If idSala provided, load mesas
$selectedSala = isset($_GET['idSala']) ? intval($_GET['idSala']) : (count($salas) ? intval($salas[0]['idSala']) : 0);

$mesas = [];
if ($selectedSala) {
  $stmt = $conn->prepare("SELECT idMesa, nombre, estado FROM mesa WHERE idSala = ? ORDER BY nombre");
  $stmt->execute([$selectedSala]);
  $mesas = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Selected date for listing reservations (GET param or today)
$selectedDate = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');

// Load reservations for selected sala and date
$reservas = [];
if ($selectedSala) {
  try {
    $stmtR = $conn->prepare(
      "SELECT r.*, m.nombre AS mesaNombre, u.nombre AS usuarioNombre, u.apellidos AS usuarioApellidos, c.nombre AS camNombre, c.apellidos AS camApellidos
       FROM reserva r
       LEFT JOIN mesa m ON r.idMesa = m.idMesa
       LEFT JOIN usuario u ON r.idUsuario = u.idUsuario
       LEFT JOIN camarero c ON r.idCamarero = c.idCamarero
       WHERE r.idSala = :idSala AND r.fecha = :fecha
       ORDER BY r.horaInicio"
    );
    $stmtR->execute([':idSala' => $selectedSala, ':fecha' => $selectedDate]);
    $reservas = $stmtR->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    $reservas = [];
    $errorMsg = 'Error cargando reservas: ' . $e->getMessage();
  }
}

// Flash
$errorMsg = $_SESSION['error'] ?? ($errorMsg ?? '');
$successMsg = $_SESSION['success'] ?? '';
unset($_SESSION['error'], $_SESSION['success']);
?>
