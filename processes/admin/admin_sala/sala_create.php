<?php
session_start();
require_once __DIR__ . '/../../../database/conexion.php';

if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], ['admin', 'manteniment'])) {
    header('Location: ../../../pages/admin/admin_salas.php?error=NoPermisos');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../../pages/admin/admin_salas.php');
    exit;
}

$id = isset($_POST['idSala']) && $_POST['idSala'] !== '' ? intval($_POST['idSala']) : 0;
$nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';

if ($nombre === '') {
    header('Location: ../../../pages/admin/admin_salas.php?error=NombreRequerido');
    exit;
}

try {
    if ($id > 0) {
        $stmt = $conn->prepare('UPDATE sala SET nombre = :n WHERE idSala = :id');
        $stmt->execute([':n'=>$nombre,':id'=>$id]);
    } else {
        $stmt = $conn->prepare('INSERT INTO sala (nombre) VALUES (:n)');
        $stmt->execute([':n'=>$nombre]);
    }
    // Manejo de la imagen de fondo (si se ha subido)
    if (isset($_FILES['fondo']) && $_FILES['fondo']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($_FILES['fondo']['type'], $allowed)) {
            // Ruta corregida: desde processes/admin/admin_sala/ hasta img/regiones/
            $destDir = __DIR__ . '/../../../img/regiones/';
            if (!is_dir($destDir)) {
                mkdir($destDir, 0755, true);
            }
            $ext = pathinfo($_FILES['fondo']['name'], PATHINFO_EXTENSION);
            // Determinar ID de la sala
            $salaId = $id > 0 ? $id : $conn->lastInsertId();
            $fileName = 'sala_' . $salaId . '.' . $ext;
            $destPath = $destDir . $fileName;
            move_uploaded_file($_FILES['fondo']['tmp_name'], $destPath);
        }
    }
    header('Location: ../../../pages/admin/admin_salas.php?ok=1');
    exit;
} catch (PDOException $e) {
    header('Location: ../../../pages/admin/admin_salas.php?error=' . urlencode($e->getMessage()));
    exit;
}
