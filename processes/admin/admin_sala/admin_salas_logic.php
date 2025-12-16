<?php
session_start();
require_once __DIR__ . '/../../../database/conexion.php';

// Aceptar idCamarero (sistema antiguo) o idUsuario + rol admin (sistema nuevo)
// Verificar permisos (Admin o Manteniment)
// Verificar permisos (Admin o Manteniment)
if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], ['admin', 'manteniment'])) {
    $access_denied = true;
    $denied_message = "No tienes permisos para acceder a esta página (Gestión de Salas).";
     $salas = [];
     $editSala = null;
     $msg = '';
} else {
    $access_denied = false;
}

if (!$access_denied) {

$msg = '';
try {
    $stmt = $conn->query('SELECT * FROM sala ORDER BY idSala');
    $salas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $msg = 'Error cargando salas: ' . $e->getMessage();
    $salas = [];
}

$editSala = null;
if (isset($_GET['edit']) && intval($_GET['edit']) > 0) {
    $id = intval($_GET['edit']);
    $stmt = $conn->prepare('SELECT * FROM sala WHERE idSala = :id');
    $stmt->execute([':id' => $id]);
    $editSala = $stmt->fetch(PDO::FETCH_ASSOC);
}
} // End if !$access_denied
