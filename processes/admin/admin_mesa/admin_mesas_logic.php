<?php
session_start();
require_once __DIR__ . '/../../../database/conexion.php';

// Aceptar idCamarero (sistema antiguo) o idUsuario + rol admin (sistema nuevo)
// Verificar permisos (Admin o Manteniment)
if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], ['admin', 'manteniment'])) {
    $access_denied = true;
    $denied_message = "No tienes permisos para acceder a esta página (Gestión de Mesas).";
    // Initialize vars to empty defaults
    $salas = [];
    $mesas = [];
    $editMesa = null;
    $idSala = 0;
} else {
    $access_denied = false;
}

if (!$access_denied) {

require_once __DIR__ . '/sync_mesas.php';
syncMesasStatus($conn);

$idSala = isset($_GET['idSala']) ? intval($_GET['idSala']) : 0;

// obtener salas para el selector
$salas = [];
try {
    $stmt = $conn->query('SELECT * FROM sala ORDER BY idSala');
    $salas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { }

// si no se indica sala, coger la primera
if ($idSala <= 0 && count($salas) > 0) $idSala = intval($salas[0]['idSala']);

// obtener mesas de la sala
$mesas = [];
try {
    $stmt = $conn->prepare('SELECT * FROM mesa WHERE idSala = :idSala ORDER BY idMesa');
    $stmt->execute([':idSala' => $idSala]);
    $mesas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { }

$editMesa = null;
if (isset($_GET['edit']) && intval($_GET['edit']) > 0) {
    $id = intval($_GET['edit']);
    $stmt = $conn->prepare('SELECT * FROM mesa WHERE idMesa = :id');
    $stmt->execute([':id' => $id]);
    $editMesa = $stmt->fetch(PDO::FETCH_ASSOC);
}
} // End if !$access_denied
