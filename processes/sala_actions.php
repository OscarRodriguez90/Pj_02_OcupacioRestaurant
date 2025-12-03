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

// --- 1. PARÁMETROS GLOBALES ---
// Obtenemos idSala y filtros, ya sea por GET o POST
$idSala = isset($_GET['idSala']) ? intval($_GET['idSala']) : (isset($_POST['idSala']) ? intval($_POST['idSala']) : 0);
$filtro_estado = $_REQUEST['filtro_estado'] ?? 'todas';
$filtro_sillas = $_REQUEST['filtro_sillas'] ?? 'todas';

// Validación básica
if ($idSala <= 0) {
    // Si no hay sala válida, redirigir a selección
    if (!headers_sent()) {
        header('Location: ../selecciona_sala.php');
        exit;
    }
}

// URL base para redirecciones (mantiene el contexto)
$baseUrl = "sala.php?idSala=$idSala&filtro_estado=$filtro_estado&filtro_sillas=$filtro_sillas";

// --- 2. LÓGICA DE ACCIONES (POST) ---
// Se ejecuta cuando se envía un formulario a esta misma página
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $idCamarero = $_SESSION['idCamarero'] ?? null;
    
    // Si no hay sesión, redirigir a login
    if (!$idCamarero) {
        header('Location: ../login.php?error=SesionExpirada');
        exit;
    }

    try {
        // A) ACCIONES MASIVAS (Ocupar/Liberar todas)
        if (isset($_POST['accion_todas'])) {
            $accion = $_POST['accion_todas'];

            if ($accion === 'ocupar_todas') {
                $sql = "SELECT idMesa FROM mesa WHERE idSala = :idSala AND estado = 'libre'";
                $stmt = $conn->prepare($sql);
                $stmt->execute([':idSala' => $idSala]);
                $mesas_libres = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($mesas_libres as $mesa) {
                    $upd = $conn->prepare("UPDATE mesa SET estado = 'ocupada' WHERE idMesa = :id");
                    $upd->execute([':id' => $mesa['idMesa']]);

                    $insertHist = $conn->prepare("INSERT INTO historico (idMesa, idSala, idCamarero, horaOcupacion, horaDesocupacion) VALUES (:idMesa, :idSala, :idCamarero, NOW(), NULL)");
                    $insertHist->execute([':idMesa' => $mesa['idMesa'], ':idSala' => $idSala, ':idCamarero' => $idCamarero]);
                }
                $_SESSION['success'] = "Todas las mesas libres han sido ocupadas";

            } elseif ($accion === 'liberar_todas') {
                $sql = "SELECT m.idMesa FROM mesa m INNER JOIN historico h ON m.idMesa = h.idMesa WHERE m.idSala = :idSala AND m.estado = 'ocupada' AND h.horaDesocupacion IS NULL AND h.idCamarero = :idCamarero";
                $stmt = $conn->prepare($sql);
                $stmt->execute([':idSala' => $idSala, ':idCamarero' => $idCamarero]);
                $mis_mesas_ocupadas = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($mis_mesas_ocupadas as $mesa) {
                    $upd = $conn->prepare("UPDATE mesa SET estado = 'libre' WHERE idMesa = :id");
                    $upd->execute([':id' => $mesa['idMesa']]);

                    $updateHist = $conn->prepare("UPDATE historico SET horaDesocupacion = NOW() WHERE idMesa = :idMesa AND horaDesocupacion IS NULL AND idCamarero = :idCamarero");
                    $updateHist->execute([':idMesa' => $mesa['idMesa'], ':idCamarero' => $idCamarero]);
                }
                $_SESSION['success'] = "Todas tus mesas ocupadas han sido liberadas";
            }
            // Redirigir para evitar reenvío de formulario
            header("Location: $baseUrl");
            exit;
        }
        
        // B) ACTUALIZAR NÚMERO DE SILLAS
        elseif (isset($_POST['actualizar_sillas'])) {
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
        
        // C) CAMBIAR ESTADO INDIVIDUAL (Ocupar/Liberar una mesa)
        elseif (isset($_POST['idMesa'])) {
            $idMesa = intval($_POST['idMesa']);
            
            $stmt = $conn->prepare("SELECT estado FROM mesa WHERE idMesa = :id");
            $stmt->execute([':id' => $idMesa]);
            $mesa = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($mesa) {
                if ($mesa['estado'] === 'libre') {
                    // Ocupar
                    $upd = $conn->prepare("UPDATE mesa SET estado = 'ocupada' WHERE idMesa = :id");
                    $upd->execute([':id' => $idMesa]);

                    $insertHist = $conn->prepare("INSERT INTO historico (idMesa, idSala, idCamarero, horaOcupacion, horaDesocupacion) VALUES (:idMesa, :idSala, :idCamarero, NOW(), NULL)");
                    $insertHist->execute([':idMesa' => $idMesa, ':idSala' => $idSala, ':idCamarero' => $idCamarero]);
                } else {
                    // Liberar (verificando propiedad)
                    $stmtHist = $conn->prepare("SELECT idCamarero FROM historico WHERE idMesa = :idMesa AND horaDesocupacion IS NULL ORDER BY idHistorico DESC LIMIT 1");
                    $stmtHist->execute([':idMesa' => $idMesa]);
                    $historico = $stmtHist->fetch(PDO::FETCH_ASSOC);

                    if (!$historico || $historico['idCamarero'] != $idCamarero) {
                        $_SESSION['error'] = "No puedes liberar esta mesa. Solo el camarero que la ocupó puede liberarla.";
                    } else {
                        $upd = $conn->prepare("UPDATE mesa SET estado = 'libre' WHERE idMesa = :id");
                        $upd->execute([':id' => $idMesa]);

                        $updateHist = $conn->prepare("UPDATE historico SET horaDesocupacion = NOW() WHERE idMesa = :idMesa AND horaDesocupacion IS NULL ORDER BY idHistorico DESC LIMIT 1");
                        $updateHist->execute([':idMesa' => $idMesa]);
                    }
                }
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

// A) Obtener Mesas con Filtros
try {
    $sql = "SELECT * FROM mesa WHERE idSala = :idSala";
    if ($filtro_estado === 'ocupadas') $sql .= " AND estado = 'ocupada'";
    if ($filtro_estado === 'libres') $sql .= " AND estado = 'libre'";
    if (in_array($filtro_sillas, ['1','2','3','4'])) $sql .= " AND numSillas = " . intval($filtro_sillas);
    $sql .= " ORDER BY nombre";

    $stmt = $conn->prepare($sql);
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
        $sql = "SELECT c.nombre, c.apellidos, c.idCamarero FROM historico h INNER JOIN camarero c ON h.idCamarero = c.idCamarero WHERE h.idMesa = :idMesa AND h.horaDesocupacion IS NULL ORDER BY h.idHistorico DESC LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':idMesa' => $selectedMesa['idMesa']]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $nombreCamareroOcupante = $row['nombre'] . ' ' . $row['apellidos'];
            $puedeLiberar = ($row['idCamarero'] == $_SESSION['idCamarero']);
        }
    }
}

// C) Mensajes Flash
if (isset($_SESSION['error'])) { $errorMsg = $_SESSION['error']; unset($_SESSION['error']); }
if (isset($_SESSION['success'])) { $successMsg = $_SESSION['success']; unset($_SESSION['success']); }

// D) Información de la Sala
try {
    $stmtSala = $conn->prepare("SELECT nombre FROM sala WHERE idSala = :id");
    $stmtSala->execute([':id' => $idSala]);
    $salaRow = $stmtSala->fetch(PDO::FETCH_ASSOC);
    $nombreSala = $salaRow ? ucfirst($salaRow['nombre']) : 'Sala';
    // Determine background image file for this sala (if exists)
    $fondoSala = '';
    $imgPattern = __DIR__ . '/../../img/salas/sala_' . $idSala . '.*';
    $matches = glob($imgPattern);
    if ($matches && count($matches) > 0) {
        // Use the first matching file name
        $fondoSala = basename($matches[0]);
    }
} catch (PDOException $e) {
    $nombreSala = 'Sala';
}
