<?php
session_start();
require_once __DIR__ . '/../database/conexion.php';

$rol = $_SESSION['rol'] ?? null;
if (!isset($_SESSION['idCamarero']) && $rol !== 'admin') {
    header('Location: ../login.php?error=SesionExpirada');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: ../pages/admin_mesas.php');
    exit;
}

$id = intval($_GET['id']);
$idSala = isset($_GET['idSala']) ? intval($_GET['idSala']) : 0;
try {
    // borrar historico asociado a la mesa, luego la mesa
    $conn->beginTransaction();

    $stmt = $conn->prepare('DELETE FROM historico WHERE idMesa = :id');
    $stmt->execute([':id' => $id]);

    $stmt = $conn->prepare('DELETE FROM mesa WHERE idMesa = :id');
    $stmt->execute([':id' => $id]);

    $conn->commit();
    header('Location: ../pages/admin_mesas.php?idSala=' . $idSala);
    exit;
} catch (PDOException $e) {
    if ($conn->inTransaction()) $conn->rollBack();
    header('Location: ../pages/admin_mesas.php?idSala=' . $idSala . '&error=' . urlencode($e->getMessage()));
    exit;
}
