<?php
session_start();
require_once __DIR__ . '/../database/conexion.php';

$rol = $_SESSION['rol'] ?? null;
if (!isset($_SESSION['idCamarero']) && $rol !== 'admin') {
    header('Location: ../login.php?error=SesionExpirada');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/admin_mesas.php');
    exit;
}

$id = isset($_POST['idMesa']) && $_POST['idMesa'] !== '' ? intval($_POST['idMesa']) : 0;
$nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
$numSillas = isset($_POST['numSillas']) ? intval($_POST['numSillas']) : 2;
$idSala = isset($_POST['idSala']) ? intval($_POST['idSala']) : 0;
$estado = isset($_POST['estado']) && in_array($_POST['estado'], ['libre','ocupada']) ? $_POST['estado'] : 'libre';

if ($nombre === '' || $idSala<=0 || $numSillas<=0) {
    header('Location: ../pages/admin_mesas.php?error=DatosInvalidos');
    exit;
}

try {
    if ($id > 0) {
        $stmt = $conn->prepare('UPDATE mesa SET nombre=:n, numSillas=:ns, estado=:e, idSala=:ids WHERE idMesa=:id');
        $stmt->execute([':n'=>$nombre,':ns'=>$numSillas,':e'=>$estado,':ids'=>$idSala,':id'=>$id]);
    } else {
        $stmt = $conn->prepare('INSERT INTO mesa (nombre, numSillas, estado, idSala) VALUES (:n,:ns,:e,:ids)');
        $stmt->execute([':n'=>$nombre,':ns'=>$numSillas,':e'=>$estado,':ids'=>$idSala]);
    }
    header('Location: ../pages/admin_mesas.php?idSala=' . $idSala);
    exit;
} catch (PDOException $e) {
    header('Location: ../pages/admin_mesas.php?idSala=' . $idSala . '&error=' . urlencode($e->getMessage()));
    exit;
}
