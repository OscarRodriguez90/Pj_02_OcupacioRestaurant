<?php
session_start();
include '../database/conexion.php';

if (!isset($_GET['id'])) {
    header('Location: ../pages/reservas.php');
    exit();
}

$idReserva = intval($_GET['id']);

try {
    $stmt = $conn->prepare("SELECT * FROM reserva WHERE idReserva = ? LIMIT 1");
    $stmt->execute([$idReserva]);
    $reserva = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reserva) {
        $_SESSION['error'] = 'Reserva no encontrada.';
        header('Location: ../pages/reservas.php');
        exit();
    }

    $idCamareroSess = $_SESSION['idCamarero'] ?? null;
    $idUsuarioSess = $_SESSION['idUsuario'] ?? null;
    $rol = $_SESSION['rol'] ?? null;

    $isOwner = false;
    if ($rol === 'admin') {
        $isOwner = true;
    } else {
        // El camarero puede cancelar su propia reserva
        if ($idCamareroSess && $reserva['idCamarero'] == $idCamareroSess) {
            $isOwner = true;
        }
        // El usuario (del sistema nuevo) puede cancelar su propia reserva
        if ($idUsuarioSess && $reserva['idUsuario'] == $idUsuarioSess) {
            $isOwner = true;
        }
    }

    if (!$isOwner) {
        $_SESSION['error'] = 'No tienes permiso para eliminar esta reserva. Solo el administrador o quien hizo la reserva puede cancelarla.';
        $redirect = '../pages/reservas.php?idSala=' . intval($reserva['idSala']);
        header('Location: ' . $redirect);
        exit();
    }

    $del = $conn->prepare("DELETE FROM reserva WHERE idReserva = ?");
    $del->execute([$idReserva]);

    $_SESSION['success'] = 'Reserva cancelada correctamente.';
    $redirect = '../pages/reservas.php?idSala=' . intval($reserva['idSala']);
    header('Location: ' . $redirect);
    exit();

} catch (PDOException $e) {
    $_SESSION['error'] = 'Error eliminando la reserva: ' . $e->getMessage();
    header('Location: ../pages/reservas.php');
    exit();
}
?>