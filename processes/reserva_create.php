<?php
session_start();
include '../database/conexion.php';

// Allow camarero or admin to create reservations
if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], ['camarero', 'admin'])) {
    $_SESSION['error'] = "No tienes permisos para crear reservas.";
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // If accessed directly, send back to sala selection
    header("Location: ../pages/selecciona_sala.php");
    exit();
}

$idSala = intval($_POST['idSala'] ?? 0);
$idMesa = intval($_POST['idMesa'] ?? 0);
$fecha = $_POST['fecha'] ?? '';
$horaInicio = $_POST['horaInicio'] ?? '';
$horaFin = $_POST['horaFin'] ?? '';

// Validar que las horas no estén vacías
if (empty($horaInicio)) {
    $_SESSION['error'] = "No se recibió hora de inicio. Por favor selecciona una franja horaria válida.";
    header("Location: ../pages/reservas.php?idSala=" . $idSala);
    exit();
}

if (empty($horaFin)) {
    $_SESSION['error'] = "No se recibió hora de fin. Por favor selecciona una franja horaria válida.";
    header("Location: ../pages/reservas.php?idSala=" . $idSala);
    exit();
}

$errors = [];

if ($idSala <= 0) $errors[] = "Sala inválida.";
if ($idMesa <= 0) $errors[] = "Mesa inválida.";
if (empty($fecha) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) $errors[] = "Fecha inválida. Usa el formato YYYY-MM-DD.";
if (empty($horaInicio) || !preg_match('/^\d{2}:\d{2}$/', $horaInicio)) $errors[] = "Hora de inicio inválida.";
if (empty($horaFin) || !preg_match('/^\d{2}:\d{2}$/', $horaFin)) $errors[] = "Hora de fin inválida.";

if (empty($errors)) {
    if ($horaInicio >= $horaFin) $errors[] = "La hora de inicio debe ser anterior a la hora de fin.";
    
    // Validar que la fecha no sea en el pasado y que la franja no empiece/termine en el pasado
    try {
        $now = new DateTime();
        $start = DateTime::createFromFormat('Y-m-d H:i', $fecha . ' ' . $horaInicio);
        $end = DateTime::createFromFormat('Y-m-d H:i', $fecha . ' ' . $horaFin);
        if ($start === false || $end === false) {
            $errors[] = "Formato de fecha/hora inválido.";
        } else {
            // No reservar en fechas pasadas
            if ($end <= $now) {
                $errors[] = "La franja seleccionada ya ha pasado.";
            }
            // No permitir inicio en pasado o en el presente (requiere empezar en el futuro)
            if ($start <= $now) {
                $errors[] = "La hora de inicio debe ser posterior a la hora actual.";
            }
        }
    } catch (Exception $e) {
        $errors[] = "Error validando fecha/hora: " . $e->getMessage();
    }
}

if (!empty($errors)) {
    $_SESSION['error'] = implode("<br>", $errors);
    if (isset($_POST['from']) && $_POST['from'] === 'reservas') {
        header("Location: ../pages/reservas.php?idSala=" . $idSala);
    } else {
        header("Location: ../pages/salas/sala.php?idSala=" . $idSala);
    }
    exit();
}

try {
    // Check for overlapping reservations on same mesa and date
    $stmt = $conn->prepare(
        "SELECT COUNT(*) FROM reserva WHERE idMesa = :idMesa AND fecha = :fecha AND NOT (horaFin <= :horaInicio OR horaInicio >= :horaFin)"
    );
    $stmt->execute([
        ':idMesa' => $idMesa,
        ':fecha' => $fecha,
        ':horaInicio' => $horaInicio,
        ':horaFin' => $horaFin
    ]);
    $count = $stmt->fetchColumn();
    if ($count > 0) {
        $_SESSION['error'] = "Ya existe una reserva conflictiva para esa mesa en la franja horaria indicada.";
        header("Location: ../pages/salas/sala.php?idSala=" . $idSala . "&select=" . $idMesa);
        exit();
    }

    // Insert
    $idCamarero = isset($_SESSION['idCamarero']) ? intval($_SESSION['idCamarero']) : null;
    $idUsuario = isset($_SESSION['idUsuario']) ? intval($_SESSION['idUsuario']) : null;

    $insert = $conn->prepare(
        "INSERT INTO reserva (idMesa, idSala, idCamarero, idUsuario, fecha, horaInicio, horaFin) VALUES (:idMesa, :idSala, :idCamarero, :idUsuario, :fecha, :horaInicio, :horaFin)"
    );
    $result = $insert->execute([
        ':idMesa' => $idMesa,
        ':idSala' => $idSala,
        ':idCamarero' => $idCamarero,
        ':idUsuario' => $idUsuario,
        ':fecha' => $fecha,
        ':horaInicio' => $horaInicio,
        ':horaFin' => $horaFin
    ]);

    if (!$result) {
        $_SESSION['error'] = "Error al crear la reserva. Por favor intenta de nuevo.";
        header("Location: ../pages/reservas.php?idSala=" . $idSala);
        exit();
    }

    // Check if the reservation is active right now
    $currentDate = date('Y-m-d');
    $currentTime = date('H:i');
    
    if ($fecha === $currentDate && $horaInicio <= $currentTime && $horaFin > $currentTime) {
        $updateMesa = $conn->prepare("UPDATE mesa SET estado = 'ocupada' WHERE idMesa = :idMesa");
        $updateMesa->execute([':idMesa' => $idMesa]);
    }

    // Set success message
    $_SESSION['success'] = "Reserva creada correctamente. Franja: " . $horaInicio . " - " . $horaFin;
    // Redirect depending on origin
    if (isset($_POST['from']) && $_POST['from'] === 'reservas') {
        header("Location: ../pages/reservas.php?idSala=" . $idSala);
    } else {
        header("Location: ../pages/salas/sala.php?select=" . $idMesa . "&idSala=" . $idSala);
    }
    exit();

} catch (PDOException $e) {
    $_SESSION['error'] = "Error guardando la reserva: " . $e->getMessage();
    if (isset($_POST['from']) && $_POST['from'] === 'reservas') {
        header("Location: ../pages/reservas.php?idSala=" . $idSala);
    } else {
        header("Location: ../pages/salas/sala.php?select=" . $idMesa . "&idSala=" . $idSala);
    }
    exit();
}

?>