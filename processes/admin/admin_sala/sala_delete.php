<?php
session_start();
require_once __DIR__ . '/../../../database/conexion.php';

$rol = $_SESSION['rol'] ?? null;
if (!isset($rol) || !in_array($rol, ['admin', 'manteniment'])) {
    header('Location: ../../../pages/admin/admin_salas.php?error=NoPermisos');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: ../../../pages/admin/admin_salas.php');
    exit;
}

$id = intval($_GET['id']);
try {
    // Borrar en cascada manualmente: historico -> mesas -> sala
    $conn->beginTransaction();

    // borrar historico asociado a la sala
    $stmt = $conn->prepare('DELETE FROM historico WHERE idSala = :id');
    $stmt->execute([':id' => $id]);

    // borrar historico asociado a las mesas de la sala (por seguridad)
    $stmt = $conn->prepare('SELECT idMesa FROM mesa WHERE idSala = :id');
    $stmt->execute([':id' => $id]);
    $mesas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if (count($mesas) > 0) {
        $in = implode(',', array_fill(0, count($mesas), '?'));
        $delHist = $conn->prepare("DELETE FROM historico WHERE idMesa IN ($in)");
        $delHist->execute($mesas);
    }

    // borrar mesas de la sala
    $stmt = $conn->prepare('DELETE FROM mesa WHERE idSala = :id');
    $stmt->execute([':id' => $id]);

    // finalmente borrar la sala
    $stmt = $conn->prepare('DELETE FROM sala WHERE idSala = :id');
    $stmt->execute([':id' => $id]);

    $conn->commit();
    header('Location: ../../../pages/admin/admin_salas.php?ok=deleted');
    exit;
} catch (PDOException $e) {
    if ($conn->inTransaction()) $conn->rollBack();
    header('Location: ../../../pages/admin/admin_salas.php?error=' . urlencode($e->getMessage()));
    exit;
}
