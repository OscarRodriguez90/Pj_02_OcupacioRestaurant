<?php
session_start();

require_once '../../database/conexion.php';

// Obtener idSala desde GET
$idSala = isset($_GET['idSala']) ? intval($_GET['idSala']) : 0;

if ($idSala <= 0) {
    header('Location: ../selecciona_sala.php');
    exit;
}

// --- 1. CAMBIAR ESTADO DE MESA INDIVIDUAL ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idMesa'])) {
    $idMesa = intval($_POST['idMesa']);
    $idCamarero = $_SESSION['idCamarero'] ?? null;

    if (!$idCamarero) {
        header('Location: ../login.php?error=SesionExpirada');
        exit;
    }

    try {
        $stmt = $conn->prepare("SELECT estado FROM mesa WHERE idMesa = :id");
        $stmt->execute([':id' => $idMesa]);
        $mesa = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($mesa) {
            if ($mesa['estado'] === 'libre') {
                $nuevoEstado = 'ocupada';
                $upd = $conn->prepare("UPDATE mesa SET estado = :estado WHERE idMesa = :id");
                $upd->execute([':estado' => $nuevoEstado, ':id' => $idMesa]);

                $insertHist = $conn->prepare(
                    "INSERT INTO historico (idMesa, idSala, idCamarero, horaOcupacion, horaDesocupacion) VALUES (:idMesa, :idSala, :idCamarero, NOW(), NULL)"
                );
                $insertHist->execute([
                    ':idMesa' => $idMesa,
                    ':idSala' => $idSala,
                    ':idCamarero' => $idCamarero
                ]);

            } else {
                $stmtHist = $conn->prepare(
                    "SELECT idCamarero FROM historico WHERE idMesa = :idMesa AND horaDesocupacion IS NULL ORDER BY idHistorico DESC LIMIT 1"
                );
                $stmtHist->execute([':idMesa' => $idMesa]);
                $historico = $stmtHist->fetch(PDO::FETCH_ASSOC);

                if (!$historico || $historico['idCamarero'] != $idCamarero) {
                    $_SESSION['error'] = "No puedes liberar esta mesa. Solo el camarero que la ocupó puede liberarla.";
                    header('Location: ./sala.php?select=' . $idMesa . '&idSala=' . $idSala);
                    exit;
                }

                $nuevoEstado = 'libre';
                $upd = $conn->prepare("UPDATE mesa SET estado = :estado WHERE idMesa = :id");
                $upd->execute([':estado' => $nuevoEstado, ':id' => $idMesa]);

                $updateHist = $conn->prepare(
                    "UPDATE historico SET horaDesocupacion = NOW() WHERE idMesa = :idMesa AND horaDesocupacion IS NULL ORDER BY idHistorico DESC LIMIT 1"
                );
                $updateHist->execute([':idMesa' => $idMesa]);
            }
        }
    } catch (PDOException $e) {
        $errorMsg = "Error al cambiar estado: " . $e->getMessage();
    }

    header('Location: ./sala.php?select=' . $idMesa . '&idSala=' . $idSala);
    exit;
}

// --- 2. ACTUALIZAR NÚMERO DE SILLAS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_sillas'])) {
    $idMesa = intval($_POST['idMesa']);
    $nuevoNumSillas = intval($_POST['num_sillas']);
    $idCamarero = $_SESSION['idCamarero'] ?? null;

    if (!$idCamarero) {
        header('Location: ../login.php?error=SesionExpirada');
        exit;
    }

    try {
        if ($nuevoNumSillas < 1 || $nuevoNumSillas > 10) {
            $_SESSION['error'] = "El número de sillas debe estar entre 1 y 10";
            header('Location: ./sala.php?select=' . $idMesa . '&idSala=' . $idSala);
            exit;
        }

        $upd = $conn->prepare("UPDATE mesa SET numSillas = :numSillas WHERE idMesa = :id");
        $upd->execute([':numSillas' => $nuevoNumSillas, ':id' => $idMesa]);

        $_SESSION['success'] = "Número de sillas actualizado correctamente a $nuevoNumSillas";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error al actualizar sillas: " . $e->getMessage();
    }

    header('Location: ./sala.php?select=' . $idMesa . '&idSala=' . $idSala);
    exit;
}

// --- 3. CAMBIAR ESTADO DE TODAS LAS MESAS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion_todas'])) {
    $idCamarero = $_SESSION['idCamarero'] ?? null;
    if (!$idCamarero) {
        header('Location: ../login.php?error=SesionExpirada');
        exit;
    }

    try {
        $accion = $_POST['accion_todas'];

        if ($accion === 'ocupar_todas') {
            $sql = "SELECT idMesa FROM mesa WHERE idSala = :idSala AND estado = 'libre'";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':idSala' => $idSala]);
            $mesas_libres = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($mesas_libres as $mesa) {
                $upd = $conn->prepare("UPDATE mesa SET estado = 'ocupada' WHERE idMesa = :id");
                $upd->execute([':id' => $mesa['idMesa']]);

                $insertHist = $conn->prepare(
                    "INSERT INTO historico (idMesa, idSala, idCamarero, horaOcupacion, horaDesocupacion) VALUES (:idMesa, :idSala, :idCamarero, NOW(), NULL)"
                );
                $insertHist->execute([
                    ':idMesa' => $mesa['idMesa'],
                    ':idSala' => $idSala,
                    ':idCamarero' => $idCamarero
                ]);
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

                $updateHist = $conn->prepare(
                    "UPDATE historico SET horaDesocupacion = NOW() WHERE idMesa = :idMesa AND horaDesocupacion IS NULL AND idCamarero = :idCamarero"
                );
                $updateHist->execute([':idMesa' => $mesa['idMesa'], ':idCamarero' => $idCamarero]);
            }

            $_SESSION['success'] = "Todas tus mesas ocupadas han sido liberadas";
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error al cambiar estado de las mesas: " . $e->getMessage();
    }

    header('Location: ./sala.php?idSala=' . $idSala);
    exit;
}

// FILTROS
$filtro_estado = $_GET['filtro_estado'] ?? 'todas';
$filtro_sillas = $_GET['filtro_sillas'] ?? 'todas';

try {
    $sql = "SELECT * FROM mesa WHERE idSala = :idSala";

    if ($filtro_estado === 'ocupadas') $sql .= " AND estado = 'ocupada'";
    if ($filtro_estado === 'libres') $sql .= " AND estado = 'libre'";

    if ($filtro_sillas === '1') $sql .= " AND numSillas = 1";
    elseif ($filtro_sillas === '2') $sql .= " AND numSillas = 2";
    elseif ($filtro_sillas === '3') $sql .= " AND numSillas = 3";
    elseif ($filtro_sillas === '4') $sql .= " AND numSillas = 4";

    $sql .= " ORDER BY nombre";

    $stmt = $conn->prepare($sql);
    $stmt->execute([':idSala' => $idSala]);
    $mesas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $mesas = [];
    $errorMsg = "Error al leer mesas: " . $e->getMessage();
}

// Sala seleccionada
$selectedMesa = null;
$nombreCamareroOcupante = null;
$puedeLiberar = false;
$idCamareroOcupante = null;

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
            $idCamareroOcupante = $row['idCamarero'];
            $puedeLiberar = ($idCamareroOcupante == $_SESSION['idCamarero']);
        }
    }
}

if (isset($_SESSION['error'])) { $errorMsg = $_SESSION['error']; unset($_SESSION['error']); }
if (isset($_SESSION['success'])) { $successMsg = $_SESSION['success']; unset($_SESSION['success']); }

// Obtener nombre de sala
try {
    $stmtSala = $conn->prepare("SELECT nombre FROM sala WHERE idSala = :id");
    $stmtSala->execute([':id' => $idSala]);
    $salaRow = $stmtSala->fetch(PDO::FETCH_ASSOC);
    $nombreSala = $salaRow ? ucfirst($salaRow['nombre']) : 'Sala';
} catch (PDOException $e) {
    $nombreSala = 'Sala';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($nombreSala) ?></title>
    <link rel="stylesheet" href="../../styles/estilos.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="body-sinnoh page-sala">
    <header>
        <span>Pokéfull Stack | <?php echo $_SESSION['username'];?></span>
        <h1><?= htmlspecialchars($nombreSala) ?></h1>
        <form id="cerrar-sesion" action="../../processes/logout.php" method="post">
            <button type="submit" class="btn-cerrar">Cerrar sesión</button>
        </form>
    </header>

    <div class="sala-buttons">
        <!-- Enlaces a otras salas pueden mantenerse si se desea -->
    </div>

    <?php
    $total_mesas = count($mesas);
    $ocupadas = 0; $libres = 0;
    foreach ($mesas as $m) { if ($m['estado'] === 'ocupada') $ocupadas++; if ($m['estado'] === 'libre') $libres++; }

    $sillas_1 = $sillas_2 = $sillas_3 = $sillas_4 = 0;
    foreach ($mesas as $m) {
        if ($m['numSillas'] == 1) $sillas_1++; if ($m['numSillas'] == 2) $sillas_2++; if ($m['numSillas'] == 3) $sillas_3++; if ($m['numSillas'] == 4) $sillas_4++;
    }
    ?>

    <div class="filtros-bar">
        <div class="filtro-grupo">
            <span class="filtro-label">Estado:</span>
            <div class="filtro-botones">
                <a href="?filtro_estado=todas&filtro_sillas=<?= $filtro_sillas ?>&idSala=<?= $idSala ?>" class="filtro-btn <?= $filtro_estado === 'todas' ? 'filtro-activo' : '' ?>">Todas (<?= $total_mesas ?>)</a>
                <a href="?filtro_estado=ocupadas&filtro_sillas=<?= $filtro_sillas ?>&idSala=<?= $idSala ?>" class="filtro-btn <?= $filtro_estado === 'ocupadas' ? 'filtro-activo' : '' ?>">Ocupadas (<?= $ocupadas ?>)</a>
                <a href="?filtro_estado=libres&filtro_sillas=<?= $filtro_sillas ?>&idSala=<?= $idSala ?>" class="filtro-btn <?= $filtro_estado === 'libres' ? 'filtro-activo' : '' ?>">Libres (<?= $libres ?>)</a>
            </div>
        </div>

        <div class="filtro-grupo">
            <span class="filtro-label">Sillas:</span>
            <div class="filtro-botones">
                <a href="?filtro_estado=<?= $filtro_estado ?>&filtro_sillas=todas&idSala=<?= $idSala ?>" class="filtro-btn <?= $filtro_sillas === 'todas' ? 'filtro-activo' : '' ?>">Todas</a>
                <a href="?filtro_estado=<?= $filtro_estado ?>&filtro_sillas=1&idSala=<?= $idSala ?>" class="filtro-btn <?= $filtro_sillas === '1' ? 'filtro-activo' : '' ?>">1 (<?= $sillas_1 ?>)</a>
                <a href="?filtro_estado=<?= $filtro_estado ?>&filtro_sillas=2&idSala=<?= $idSala ?>" class="filtro-btn <?= $filtro_sillas === '2' ? 'filtro-activo' : '' ?>">2 (<?= $sillas_2 ?>)</a>
                <a href="?filtro_estado=<?= $filtro_estado ?>&filtro_sillas=3&idSala=<?= $idSala ?>" class="filtro-btn <?= $filtro_sillas === '3' ? 'filtro-activo' : '' ?>">3 (<?= $sillas_3 ?>)</a>
                <a href="?filtro_estado=<?= $filtro_estado ?>&filtro_sillas=4&idSala=<?= $idSala ?>" class="filtro-btn <?= $filtro_sillas === '4' ? 'filtro-activo' : '' ?>">4 (<?= $sillas_4 ?>)</a>
            </div>
        </div>

        <div class="filtro-grupo">
            <a href="./sala.php?idSala=<?= $idSala ?>" class="filtro-btn filtro-limpiar">🗙 Limpiar</a>
        </div>
    </div>

    <div class="contenedor-principal">
    <div class="info-sala">
        <h2><?= htmlspecialchars($nombreSala) ?></h2>

        <?php if (!empty($errorMsg)): ?><p class="error-msg"><?= htmlspecialchars($errorMsg) ?></p><?php endif; ?>
        <?php if (!empty($successMsg)): ?><p class="success-msg"><?= htmlspecialchars($successMsg) ?></p><?php endif; ?>

        <form method="post" action="./sala.php?idSala=<?= $idSala ?>" class="acciones-form">
            <?php if ($libres > 0): ?>
                <input type="hidden" name="accion_todas" value="ocupar_todas">
                <button type="submit" class="btn-toggle">Ocupar todas las mesas libres (<?= $libres ?>)</button>
            <?php else: ?>
                <input type="hidden" name="accion_todas" value="liberar_todas">
                <button type="submit" class="btn-toggle">Liberar todas mis mesas ocupadas</button>
            <?php endif; ?>
        </form>

        <?php if (!$selectedMesa): ?>
            <p>Mesas totales: <?= $total_mesas ?></p>
            <p>Mesas disponibles: <?= $libres ?></p>
            <p><strong>Filtros activos:</strong></p>
            <ul>
                <li>Estado: <?= $filtro_estado === 'todas' ? 'Todas' : ucfirst($filtro_estado) ?></li>
                <li>Sillas: <?= $filtro_sillas === 'todas' ? 'Todas' : $filtro_sillas ?></li>
            </ul>
            <strong>Selecciona una mesa para ver detalles.</strong>
            <br><br><br>
            <a class="btn-toggle" href="../historialSala.php?idSala=<?= $idSala ?>">Ver historial de sala</a>
        <?php else: ?>
            <p><strong>Nombre:</strong> <?= htmlspecialchars($selectedMesa['nombre']) ?></p>
            <p><strong>Estado:</strong> <?= ucfirst($selectedMesa['estado']) ?></p>
            <p><strong>Sillas:</strong> <?= intval($selectedMesa['numSillas']) ?></p>

            <form method="post" action="./sala.php?idSala=<?= $idSala ?>" class="form-sillas">
                <input type="hidden" name="idMesa" value="<?= intval($selectedMesa['idMesa']) ?>">
                <div class="form-row">
                    <label for="num_sillas"><strong>Actualizar sillas:</strong></label>
                    <input type="number" name="num_sillas" id="num_sillas" value="<?= intval($selectedMesa['numSillas']) ?>" min="1" max="10" class="input-small">
                    <button type="submit" name="actualizar_sillas" class="btn-toggle">Actualizar</button>
                </div>
            </form>

            <?php if ($selectedMesa && $selectedMesa['estado'] === 'ocupada' && $nombreCamareroOcupante): ?>
                <p><strong>Ocupada por:</strong> <?= htmlspecialchars($nombreCamareroOcupante) ?></p>
                <?php if (!$puedeLiberar): ?><p style="color: #ff6b6b; font-weight: bold;">⚠️ Solo <?= htmlspecialchars($nombreCamareroOcupante) ?> puede liberar esta mesa</p><?php endif; ?>
            <?php endif; ?>

            <?php $disabled = ($selectedMesa['estado'] === 'ocupada' && !$puedeLiberar); ?>
            <form id="form-cambiar-estado" method="post" action="./sala.php?idSala=<?= $idSala ?>&filtro_estado=<?= $filtro_estado ?>&filtro_sillas=<?= $filtro_sillas ?>">
                <input type="hidden" name="idMesa" value="<?= intval($selectedMesa['idMesa']) ?>">
                <button type="submit" class="btn-toggle <?= $disabled ? 'btn-disabled' : '' ?>" <?= $disabled ? 'disabled' : '' ?>>
                    <?= ($selectedMesa['estado'] === 'libre') ? 'Marcar como ocupada' : 'Marcar como libre' ?>
                </button>
            </form>

            <br>
            <a class="btn-toggle" href="../historial.php?idMesa=<?= intval($selectedMesa['idMesa']) ?>&idSala=<?= intval($selectedMesa['idSala']) ?>">Ver historial de mesa</a>
            <br><br><br>
            <a class="btn-toggle" href="../historialSala.php?idSala=<?= intval($selectedMesa['idSala']) ?>">Ver historial de sala</a>
        <?php endif; ?>
    </div>

    <div class="mesas-container">
        <?php if (empty($mesas)): ?>
            <p>No hay mesas disponibles con los filtros seleccionados.</p>
        <?php else: ?>
            <?php foreach ($mesas as $m): ?>
                <?php $cls = ($m['estado'] === 'ocupada') ? 'ocupada' : 'libre';
                      $url = "sala.php?select=" . intval($m['idMesa']) . "&filtro_estado=" . $filtro_estado . "&filtro_sillas=" . $filtro_sillas . "&idSala=" . $idSala;
                ?>
                <a class="mesa <?= $cls ?>" href="<?= $url ?>">
                    <div class="titulo-mesa"><?= htmlspecialchars($m['nombre']) ?></div>
                    <div class="detalle"><?= intval($m['numSillas']) ?> sillas</div>
                    <div class="estado-mesa"><?= ucfirst($m['estado']) ?></div>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<div class="volver">
    <a href="../selecciona_sala.php">← Volver a seleccionar sala</a>
</div>
<footer>
    <span>Pokéfull Stack &copy; 2025</span>
</footer>
<script src="../../js/script.js"></script>
</body>
</html>
