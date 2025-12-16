<?php
require_once '../../processes/admin/admin_sala/sala_actions.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($nombreSala) ?></title>
    <link rel="stylesheet" href="../../styles/estilos.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="body-sinnoh page-sala" <?php if (!empty($fondoSala)) { echo 'style="background-image:url(../../img/regiones/' . htmlspecialchars($fondoSala) . ');background-size:cover;background-position:center;"'; } ?>>
    <header>
        <span>Pokéfull Stack | <?php echo $_SESSION['username'];?></span>
        <h1><?= htmlspecialchars($nombreSala) ?></h1>
        <div style="display:flex;gap:8px;align-items:center;">
            <a class="btn" href="../admin/admin_salas.php">Admin Salas</a>
            <form id="cerrar-sesion" action="../../processes/logout.php" method="post" style="margin:0;">
                <button type="submit" class="btn-cerrar">Cerrar sesión</button>
            </form>
        </div>
    </header>

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
        <!-- Filter Date and Time Slot -->
        <div class="filtro-grupo">
            <span class="filtro-label">Fecha:</span>
            <form id="form-filtro-tiempo" action="" method="get" style="display:flex;gap:5px;align-items:center;">
                <input type="hidden" name="idSala" value="<?= $idSala ?>">
                <input type="hidden" name="filtro_estado" value="<?= $filtro_estado ?>">
                <input type="hidden" name="filtro_sillas" value="<?= $filtro_sillas ?>">
                <input type="date" id="filtro_fecha" name="filtro_fecha" value="<?= htmlspecialchars($filtro_fecha) ?>" class="input-filtro" style="padding:4px;border-radius:4px;">
                <select id="filtro_hora" name="filtro_hora" class="input-filtro" style="padding:4px;border-radius:4px;">
                    <?php
                    $selectedHora = $filtro_hora ?? date('H:i');
                    for ($h = 0; $h < 24; $h += 2) {
                        $start = sprintf('%02d:00', $h);
                        $end = sprintf('%02d:00', ($h + 2) % 24);
                        $label = "$start - $end";
                        $selected = ($selectedHora == $start) ? 'selected' : '';
                        echo "<option value=\"$start\" $selected>$label</option>";
                    }
                    ?>
                </select>
                <button type="submit" class="filtro-btn" style="padding:4px 8px;">Ver</button>
            </form>
        </div>

        <div class="filtro-grupo">
            <span class="filtro-label">Estado:</span>
            <div class="filtro-botones">
                <a href="?filtro_estado=todas&filtro_sillas=<?= $filtro_sillas ?>&idSala=<?= $idSala ?>&filtro_fecha=<?= $filtro_fecha ?>&filtro_hora=<?= $filtro_hora ?>" class="filtro-btn <?= $filtro_estado === 'todas' ? 'filtro-activo' : '' ?>">Todas (<?= $total_mesas ?>)</a>
                <a href="?filtro_estado=ocupadas&filtro_sillas=<?= $filtro_sillas ?>&idSala=<?= $idSala ?>&filtro_fecha=<?= $filtro_fecha ?>&filtro_hora=<?= $filtro_hora ?>" class="filtro-btn <?= $filtro_estado === 'ocupadas' ? 'filtro-activo' : '' ?>">Ocupadas (<?= $ocupadas ?>)</a>
                <a href="?filtro_estado=libres&filtro_sillas=<?= $filtro_sillas ?>&idSala=<?= $idSala ?>&filtro_fecha=<?= $filtro_fecha ?>&filtro_hora=<?= $filtro_hora ?>" class="filtro-btn <?= $filtro_estado === 'libres' ? 'filtro-activo' : '' ?>">Libres (<?= $libres ?>)</a>
            </div>
        </div>

        <div class="filtro-grupo">
            <span class="filtro-label">Sillas:</span>
            <div class="filtro-botones">
                <a href="?filtro_estado=<?= $filtro_estado ?>&filtro_sillas=todas&idSala=<?= $idSala ?>&filtro_fecha=<?= $filtro_fecha ?>&filtro_hora=<?= $filtro_hora ?>" class="filtro-btn <?= $filtro_sillas === 'todas' ? 'filtro-activo' : '' ?>">Todas</a>
                <a href="?filtro_estado=<?= $filtro_estado ?>&filtro_sillas=1&idSala=<?= $idSala ?>&filtro_fecha=<?= $filtro_fecha ?>&filtro_hora=<?= $filtro_hora ?>" class="filtro-btn <?= $filtro_sillas === '1' ? 'filtro-activo' : '' ?>">1 (<?= $sillas_1 ?>)</a>
                <a href="?filtro_estado=<?= $filtro_estado ?>&filtro_sillas=2&idSala=<?= $idSala ?>&filtro_fecha=<?= $filtro_fecha ?>&filtro_hora=<?= $filtro_hora ?>" class="filtro-btn <?= $filtro_sillas === '2' ? 'filtro-activo' : '' ?>">2 (<?= $sillas_2 ?>)</a>
                <a href="?filtro_estado=<?= $filtro_estado ?>&filtro_sillas=3&idSala=<?= $idSala ?>&filtro_fecha=<?= $filtro_fecha ?>&filtro_hora=<?= $filtro_hora ?>" class="filtro-btn <?= $filtro_sillas === '3' ? 'filtro-activo' : '' ?>">3 (<?= $sillas_3 ?>)</a>
                <a href="?filtro_estado=<?= $filtro_estado ?>&filtro_sillas=4&idSala=<?= $idSala ?>&filtro_fecha=<?= $filtro_fecha ?>&filtro_hora=<?= $filtro_hora ?>" class="filtro-btn <?= $filtro_sillas === '4' ? 'filtro-activo' : '' ?>">4 (<?= $sillas_4 ?>)</a>
            </div>
        </div>

        <div class="filtro-grupo">
            <a href="./sala.php?idSala=<?= $idSala ?>" class="filtro-btn filtro-limpiar">🗙 Limpiar</a>
        </div>
    </div>

    <div class="contenedor-principal">
    <div class="info-sala">
        <h2><?= htmlspecialchars($nombreSala) ?></h2>

        <?php if (!empty($errorMsg)): ?><p class="error-msg"><?= nl2br(htmlspecialchars($errorMsg)) ?></p><?php endif; ?>
        <?php if (!empty($successMsg)): ?><p class="success-msg"><?= nl2br(htmlspecialchars($successMsg)) ?></p><?php endif; ?>

        <?php if (!$selectedMesa): ?>
            <p>Mesas totales: <?= $total_mesas ?></p>
            <p>Mesas disponibles: <?= $libres ?></p>
            <p><strong>Filtros activos:</strong></p>
            <ul>
                <li>Estado: <?= $filtro_estado === 'todas' ? 'Todas' : ucfirst($filtro_estado) ?></li>
                <li>Sillas: <?= $filtro_sillas === 'todas' ? 'Todas' : $filtro_sillas ?></li>
            </ul>
            <strong>Selecciona una mesa para gestionar reservas.</strong>
            <br><br><br>
            <a class="btn-toggle" href="../historial/historialSala.php?idSala=<?= $idSala ?>">Ver historial de sala</a>
        <?php else: ?>
            <p><strong>Nombre:</strong> <?= htmlspecialchars($selectedMesa['nombre']) ?></p>
            <p><strong>Estado:</strong> <?= ucfirst($selectedMesa['estado']) ?></p>
            <p><strong>Sillas:</strong> <?= intval($selectedMesa['numSillas']) ?></p>

            <?php if ($selectedMesa['estado'] === 'ocupada' && $nombreCamareroOcupante): ?>
                <p><strong><?= htmlspecialchars($nombreCamareroOcupante) ?></strong></p>
            <?php endif; ?>

            <br>
            <?php if (isset($_SESSION['rol']) && in_array($_SESSION['rol'], ['camarero','admin'])): ?>
                <?php if ($selectedMesa['estado'] === 'libre'): ?>
                    <a class="btn-toggle" href="../reservas.php?idSala=<?= intval($selectedMesa['idSala']) ?>&idMesa=<?= intval($selectedMesa['idMesa']) ?>&fecha=<?= $filtro_fecha ?>&horaInicio=<?= $filtro_hora ?>">📅 Reservar Ahora</a>
                <?php else: ?>
                    <a class="btn-toggle" href="../reservas.php?idSala=<?= intval($selectedMesa['idSala']) ?>&idMesa=<?= intval($selectedMesa['idMesa']) ?>&fecha=<?= $filtro_fecha ?>">👁️ Ver / Gestionar Reserva</a>
                <?php endif; ?>
            <?php endif; ?>

            <br>
            <a class="btn-toggle" href="../historial/historial.php?idMesa=<?= intval($selectedMesa['idMesa']) ?>&idSala=<?= intval($selectedMesa['idSala']) ?>">Ver historial de mesa</a>
            <br>
            <a class="btn-toggle" href="../admin/admin_mesas.php?idSala=<?= intval($selectedMesa['idSala']) ?>&edit=<?= intval($selectedMesa['idMesa']) ?>">Administrar mesa</a>
            <br><br>
            <a class="btn-toggle" href="../historial/historialSala.php?idSala=<?= intval($selectedMesa['idSala']) ?>">Ver historial de sala</a>
        <?php endif; ?>
    </div>

    <div class="mesas-container">
        <?php if (empty($mesas)): ?>
            <p>No hay mesas disponibles con los filtros seleccionados.</p>
        <?php else: ?>
            <?php foreach ($mesas as $m): ?>
                <?php $cls = ($m['estado'] === 'ocupada') ? 'ocupada' : 'libre';
                      $url = "sala.php?select=" . intval($m['idMesa']) . "&filtro_estado=" . $filtro_estado . "&filtro_sillas=" . $filtro_sillas . "&idSala=" . $idSala . "&filtro_fecha=" . $filtro_fecha . "&filtro_hora=" . $filtro_hora;
                ?>
                <div class="mesa-wrapper">
                    <a class="mesa <?= $cls ?>" href="<?= $url ?>">
                        <div class="titulo-mesa"><?= htmlspecialchars($m['nombre']) ?></div>
                        <div class="detalle"><?= intval($m['numSillas']) ?> sillas</div>
                        <div class="estado-mesa"><?= ucfirst($m['estado']) ?></div>
                    </a>
                    <?php /* Reservar desde el menú de la mesa, no desde la tarjeta */ ?>
                </div>
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
<?php if (isset($alertMessage) && $alertMessage): ?>
    <input type="hidden" id="server-alert-msg" value="<?= htmlspecialchars($alertMessage) ?>">
<?php endif; ?>
</body>
</html>
