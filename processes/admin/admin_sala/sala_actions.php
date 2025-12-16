<?php
/**
 * LÓGICA COMPLETA DE SALA (Backend)
 * -------------------------------------------------------------------------
 * Este archivo maneja TANTO las acciones (POST) como la obtención de datos (GET).
 * Se incluye al principio de 'pages/salas/sala.php'.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../../database/conexion.php';

// Session vars disponibles para POST y GET (evita "Undefined index" y permite checks en la vista)
$idCamarero = $_SESSION['idCamarero'] ?? null;
$idUsuario = $_SESSION['idUsuario'] ?? null;
$rol = $_SESSION['rol'] ?? null;

// --- 1. PARÁMETROS GLOBALES ---
// Obtenemos idSala y filtros, ya sea por GET o POST
$idSala = isset($_GET['idSala']) ? intval($_GET['idSala']) : (isset($_POST['idSala']) ? intval($_POST['idSala']) : 0);
$filtro_estado = $_REQUEST['filtro_estado'] ?? 'todas';
$filtro_sillas = $_REQUEST['filtro_sillas'] ?? 'todas';

// --- VALIDACIÓN DE FECHA Y HORA (Filtros) ---
$filtro_fecha = $_REQUEST['filtro_fecha'] ?? date('Y-m-d');
$filtro_hora = $_REQUEST['filtro_hora'] ?? date('H:00'); // Default to current hour (approx)

// Asegurar formato de hora H:i
if (strlen($filtro_hora) == 2) $filtro_hora .= ":00"; 

$dateTimeInput = DateTime::createFromFormat('Y-m-d H:i', $filtro_fecha . ' ' . $filtro_hora);
$now = new DateTime();

$alertMessage = null;

if ($dateTimeInput && $dateTimeInput < $now) {
    // Si es hoy, permitir la franja actual (ej. son 15:30, la franja 14:00-16:00 es válida).
    // Verificamos si la franja seleccionada (2h) cubre el momento actual.
    // Inicio: filtro_hora. Fin: filtro_hora + 2h.
    $endSlot = clone $dateTimeInput;
    $endSlot->modify('+2 hours');
    
    if ($endSlot < $now) {
         // La franja ya terminó completamente. Es antigua.
         $alertMessage = "No puedes seleccionar una fecha u hora pasada.";
         // Resetear a actual
         $filtro_fecha = date('Y-m-d');
         // Buscar franja actual
         $h = intval(date('H'));
         if ($h % 2 != 0) $h--; // Round down to even
         $filtro_hora = sprintf('%02d:00', $h);
    }
}

// Validación básica
if ($idSala <= 0) {
    // Si no hay sala válida, redirigir a selección
    if (!headers_sent()) {
        header('Location: ../../../pages/selecciona_sala.php');
        exit;
    }
}

// URL base para redirecciones (mantiene el contexto)
$baseUrl = "sala.php?idSala=$idSala&filtro_estado=$filtro_estado&filtro_sillas=$filtro_sillas";

// --- 2. LÓGICA DE ACCIONES (POST) ---
// Se ejecuta cuando se envía un formulario a esta misma página
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Re-uso las variables de sesión ya definidas arriba

    // Si no hay sesión válida, redirigir a login
    if (!isset($_SESSION['username']) || !in_array($rol, ['camarero', 'admin'])) {
        header('Location: ../../../pages/login.php?error=SesionExpirada');
        exit;
    }

    try {
        // B) ACTUALIZAR NÚMERO DE SILLAS
        if (isset($_POST['actualizar_sillas'])) {
            $idMesa = intval($_POST['idMesa']);
            $nuevoNumSillas = intval($_POST['num_sillas']);

            if ($nuevoNumSillas < 1 || $nuevoNumSillas > 10) {
                $_SESSION['error'] = "El número de sillas debe estar entre 1 y 10";
            } else {
                $upd = $conn->prepare("UPDATE mesa SET numSillas = :numSillas WHERE idMesa = :id");
                $upd->execute([':numSillas' => $nuevoNumSillas, ':id' => $idMesa]);
                $_SESSION['success'] = "Número de sillas actualizado correctamente a $nuevoNumSillas";
            }
            header("Location: $baseUrl&select=$idMesa");
            exit;
        }

    } catch (PDOException $e) {
        $_SESSION['error'] = "Error en la operación: " . $e->getMessage();
        header("Location: $baseUrl");
        exit;
    }
}

// --- 3. LÓGICA DE VISTA (GET) ---
// Si llegamos aquí, es una petición GET (o POST fallido sin redirect, aunque el redirect es forzado arriba).
// Preparamos las variables para que la vista las use.

// A) Obtener Mesas con Filtros (incluyendo reservas activas)
try {
    $sql = "SELECT * FROM mesa WHERE idSala = :idSala";
    if ($filtro_estado === 'ocupadas') $sql .= " AND estado = 'ocupada'";
    if ($filtro_estado === 'libres') $sql .= " AND estado = 'libre'";
    if (in_array($filtro_sillas, ['1','2','3','4'])) $sql .= " AND numSillas = " . intval($filtro_sillas);
    $sql .= " ORDER BY nombre";

    $stmt = $conn->prepare($sql);
    $stmt->execute([':idSala' => $idSala]);
    $mesas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Sincronizar estado de las mesas
    require_once __DIR__ . '/../admin_mesa/sync_mesas.php';
    syncMesasStatus($conn, $idSala);

    // Recargar mesas con el estado actualizado
    $stmt->execute([':idSala' => $idSala]);
    $mesas = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $mesas = [];
    $errorMsg = "Error al leer mesas: " . $e->getMessage();
}

// B) Datos de la Mesa Seleccionada
$selectedMesa = null;
$nombreCamareroOcupante = null;
$puedeLiberar = false;

if (isset($_GET['select'])) {
    $idSelect = intval($_GET['select']);
    foreach ($mesas as $m) {
        if ($m['idMesa'] == $idSelect) { $selectedMesa = $m; break; }
    }

    if ($selectedMesa && $selectedMesa['estado'] === 'ocupada') {
        // Check if it is occupied by an active reservation
        $sqlReserva = "SELECT idReserva, fecha, horaInicio, horaFin FROM reserva WHERE idMesa = :idMesa AND fecha = :fecha AND horaInicio <= :horaActual AND horaFin > :horaActual LIMIT 1";
        $stmtReserva = $conn->prepare($sqlReserva);
        $stmtReserva->execute([
            ':idMesa' => $selectedMesa['idMesa'],
            ':fecha' => date('Y-m-d'),
            ':horaActual' => date('H:i:s')
        ]);
        $reservaData = $stmtReserva->fetch(PDO::FETCH_ASSOC);

        if ($reservaData) {
            $nombreCamareroOcupante = "Reservada hasta " . substr($reservaData['horaFin'], 0, 5);
            $puedeLiberar = false; // No se puede liberar una reserva desde aquí
        } else {
            // Si está ocupada por histrico
            $sql = "SELECT c.nombre, c.apellidos, c.idCamarero FROM historico h INNER JOIN camarero c ON h.idCamarero = c.idCamarero WHERE h.idMesa = :idMesa AND h.horaDesocupacion IS NULL ORDER BY h.idHistorico DESC LIMIT 1";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':idMesa' => $selectedMesa['idMesa']]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $nombreCamareroOcupante = $row['nombre'] . ' ' . $row['apellidos'];
                $ownerCamarero = $row['idCamarero'] ?? null;
                // Allow release if admin, if legacy camarero matches, or if current user is a 'camarero' in the new system
                $puedeLiberar = false;
                if (isset($rol) && $rol === 'admin') {
                    $puedeLiberar = true;
                } elseif (!empty($idCamarero) && $ownerCamarero && $ownerCamarero == $idCamarero) {
                    $puedeLiberar = true;
                } elseif (!empty($idUsuario) && isset($rol) && $rol === 'camarero') {
                    // Fallback: allow new-system camarero users to release
                    $puedeLiberar = true;
                }
            }
        }
    }
}

// C) Mensajes Flash
if (isset($_SESSION['error'])) {
    // Algunos procesos guardan arrays de errores; convertirlos a string para la vista
    if (is_array($_SESSION['error'])) {
        $errorMsg = implode("<br>", $_SESSION['error']);
    } else {
        $errorMsg = $_SESSION['error'];
    }
    unset($_SESSION['error']);
}

if (isset($_SESSION['success'])) {
    if (is_array($_SESSION['success'])) {
        $successMsg = implode("<br>", $_SESSION['success']);
    } else {
        $successMsg = $_SESSION['success'];
    }
    unset($_SESSION['success']);
}

// D) Información de la Sala
try {
    $stmtSala = $conn->prepare("SELECT nombre FROM sala WHERE idSala = :id");
    $stmtSala->execute([':id' => $idSala]);
    $salaRow = $stmtSala->fetch(PDO::FETCH_ASSOC);
    $nombreSala = $salaRow ? ucfirst($salaRow['nombre']) : 'Sala';
    // Determine background image file for this sala
    $fondoSala = '';
    
    // 1. Custom uploaded image: sala_{id}.* in img/regiones
    $imgPattern = __DIR__ . '/../img/regiones/sala_' . $idSala . '.*';
    $matches = glob($imgPattern);
    
    if ($matches && count($matches) > 0) {
        $fondoSala = basename($matches[0]);
    } else {
        // 2. Legacy image: {Nombre}.png in img/regiones
        // Limpiamos el nombre para asegurar coincidencia (aunque en DB ya debería estar bien)
        $cleanName = $salaRow['nombre']; 
        $legacyPath = __DIR__ . '/../img/regiones/' . $cleanName . '.png';
        
        if (file_exists($legacyPath)) {
            $fondoSala = $cleanName . '.png';
        }
    }
} catch (PDOException $e) {
    $nombreSala = 'Sala';
}
