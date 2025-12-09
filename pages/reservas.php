<?php
require_once '../processes/reservas_logic.php';
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Reservas</title>
  <link rel="stylesheet" href="../styles/estilos.css">
</head>
<body class="page-reservas">
  <header>
    <span>Pokéfull Stack | <?php echo $_SESSION['username']; ?></span>
    <a class="btn-ghost" href="selecciona_sala.php">Volver a selección</a>
    <form id="cerrar-sesion" action="../processes/logout.php" method="post" style="display:inline;">
        <button type="submit" class="btn-ghost">Cerrar sesión</button>
    </form>
  </header>

  <main class="contenedor-principal admin-grid">
    <section class="info-sala">
      <h2>Nueva Reserva</h2>
      <?php if (!empty($errorMsg)): ?><div class="alert-error"><?php echo nl2br(htmlspecialchars($errorMsg)); ?></div><?php endif; ?>
      <?php if (!empty($successMsg)): ?><div class="alert-success"><?php echo nl2br(htmlspecialchars($successMsg)); ?></div><?php endif; ?>

      <div class="white-box">
        <form method="POST" action="../processes/reserva_create.php">
          <div id="reservaErrors" class="form-errors" style="color:#a00;margin-bottom:8px;"></div>
          <input type="hidden" name="from" value="reservas">

          <label for="idSala">Sala *</label>
            <select id="idSala" name="idSala">
            <?php foreach ($salas as $s): ?>
              <option value="<?= intval($s['idSala']) ?>" <?= ($s['idSala']==$selectedSala) ? 'selected' : '' ?>><?= htmlspecialchars($s['nombre']) ?></option>
            <?php endforeach; ?>
          </select>

          <label for="idMesa">Mesa *</label>
            <select id="idMesa" name="idMesa">
            <?php foreach ($mesas as $m): ?>
              <option value="<?= intval($m['idMesa']) ?>" <?= ($m['estado'] !== 'libre') ? 'data-ocupada="1"' : '' ?>><?= htmlspecialchars($m['nombre']) ?> (<?= $m['estado'] ?>)</option>
            <?php endforeach; ?>
          </select>

          <label for="fecha">Fecha *</label>
            <input type="date" id="fecha" name="fecha" value="<?= htmlspecialchars(isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d')) ?>" min="<?= date('Y-m-d') ?>">

          <label for="franja">Franja horaria *</label>
            <select id="franja" name="franja">
            <option value="">-- Selecciona una franja --</option>
            <option value="08:00-10:00">08:00 - 10:00</option>
            <option value="10:00-12:00">10:00 - 12:00</option>
            <option value="12:00-14:00">12:00 - 14:00</option>
            <option value="14:00-16:00">14:00 - 16:00</option>
            <option value="16:00-18:00">16:00 - 18:00</option>
            <option value="18:00-20:00">18:00 - 20:00</option>
            <option value="20:00-22:00">20:00 - 22:00</option>
            <option value="22:00-23:59">22:00 - 23:59</option>
          </select>

          <input type="hidden" id="horaInicio" name="horaInicio" value="">
          <input type="hidden" id="horaFin" name="horaFin" value="">

          <div style="margin-top:12px;">
            <button class="btn-primary" type="submit">Reservar</button>
            <a class="btn-ghost" href="selecciona_sala.php">Cancelar</a>
          </div>
        </form>
      </div>
    </section>

    <section class="mesas-container admin-salas-section">
      <h2>Mesas</h2>
      <div class="mesas-list">
        <h3>Reservas para el día</h3>
        <form method="get" action="reservas.php" id="form-buscar-fecha">
          <div id="buscarErrors" class="form-errors" style="color:#a00;margin-bottom:8px;"></div>
          <input type="hidden" name="idSala" value="<?= intval($selectedSala) ?>">
          <label for="fecha_buscar">Fecha:</label>
          <input type="date" id="fecha_buscar" name="fecha" value="<?= htmlspecialchars($selectedDate) ?>" min="<?= date('Y-m-d') ?>">
          <button class="btn" type="submit">Ver</button>
        </form>

        <?php if (!empty($reservas)): ?>
          <table class="salas-table" style="margin-top:10px;width:100%;">
            <thead><tr><th>Mesa</th><th>Inicio</th><th>Fin</th><th>Reservado por</th><th>Acciones</th></tr></thead>
            <tbody>
            <?php foreach ($reservas as $r): ?>
              <tr>
                <td><?= htmlspecialchars($r['mesaNombre'] ?? ('#' . intval($r['idMesa']))) ?></td>
                <td><?= htmlspecialchars($r['horaInicio']) ?></td>
                <td><?= htmlspecialchars($r['horaFin']) ?></td>
                <td>
                  <?php if (!empty($r['idUsuario'])): ?>
                    <?= htmlspecialchars(($r['usuarioNombre'] ?? '') . ' ' . ($r['usuarioApellidos'] ?? '')) ?> (usuario)
                  <?php elseif (!empty($r['idCamarero'])): ?>
                    <?= htmlspecialchars(($r['camNombre'] ?? '') . ' ' . ($r['camApellidos'] ?? '')) ?> (camarero)
                  <?php else: ?>
                    —
                  <?php endif; ?>
                </td>
                <td>
                  <?php
                    $idCamareroSess = $_SESSION['idCamarero'] ?? null;
                    $idUsuarioSess = $_SESSION['idUsuario'] ?? null;
                    $rol = $_SESSION['rol'] ?? null;
                    $isOwner = false;
                    if ($rol === 'admin') {
                        $isOwner = true;
                    } else {
                        // El camarero puede cancelar su propia reserva
                        if ($idCamareroSess && $r['idCamarero'] == $idCamareroSess) {
                            $isOwner = true;
                        }
                        // El usuario (del sistema nuevo) puede cancelar su propia reserva
                        if ($idUsuarioSess && $r['idUsuario'] == $idUsuarioSess) {
                            $isOwner = true;
                        }
                    }
                  ?>
                  <?php if ($isOwner): ?>
                    <a class="btn-danger js-delete" data-confirm="¿Estás seguro de que deseas cancelar esta reserva?" href="../processes/reserva_delete.php?id=<?= intval($r['idReserva']) ?>">Cancelar</a>
                  <?php else: ?>
                    —
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        <?php else: ?>
          <p>No hay reservas para la fecha seleccionada.</p>
        <?php endif; ?>
      </div>
    </section>
  </main>

<script src="../js/script.js"></script>
</body>
</html>
