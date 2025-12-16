<?php
// Cargar lógica de reservas (sesión, permisos, salas, mesas y reservas)
require_once '../processes/admin/reserva/reservas_logic.php';
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Reservas</title>
  <link rel="stylesheet" href="../styles/estilos.css">
</head>
<!-- Header con usuario logueado y navegación -->
<body class="page-reservas">
  <header>
    <span>Pokéfull Stack | <?php echo $_SESSION['username']; ?></span>
    <a class="btn-ghost" href="selecciona_sala.php">Volver a selección</a>
    <form id="cerrar-sesion" action="../processes/logout.php" method="post" style="display:inline;">
        <button type="submit" class="btn-ghost">Cerrar sesión</button>
    </form>
  </header>

  <main class="contenedor-principal admin-grid">
    <!-- Seccion formulario -->
    <section class="info-sala">
      <h2>Nueva Reserva</h2>
      <?php if (!empty($errorMsg)): ?><div class="alert-error"><?php echo nl2br(htmlspecialchars($errorMsg)); ?></div><?php endif; ?>
      <?php if (!empty($successMsg)): ?><div class="alert-success"><?php echo nl2br(htmlspecialchars($successMsg)); ?></div><?php endif; ?>

          <div class="white-box">
        <!-- Formulario: Crear reserva con sala, mesa, fecha y franja horaria -->
        <form method="POST" action="../processes/admin/reserva/reserva_create.php">
          <div id="reservaErrors" class="form-errors" style="color:#a00;margin-bottom:8px;"></div>
          <input type="hidden" name="from" value="reservas">          <label for="idSala">Sala *</label>
            <!-- Selector: Lista de salas disponibles -->
            <select id="idSala" name="idSala">
            <?php foreach ($salas as $s): ?>
              <option value="<?= intval($s['idSala']) ?>" <?= ($s['idSala']==$selectedSala) ? 'selected' : '' ?>><?= htmlspecialchars($s['nombre']) ?></option>
            <?php endforeach; ?>
          </select>

          <label for="idMesa">Mesa *</label>
            <!-- Selector: Mesas de la sala seleccionada (muestra estado) -->
            <select id="idMesa" name="idMesa">
            <?php foreach ($mesas as $m): ?>
              <option value="<?= intval($m['idMesa']) ?>" <?= ($m['estado'] !== 'libre') ? 'data-ocupada="1"' : '' ?>><?= htmlspecialchars($m['nombre']) ?> (<?= $m['estado'] ?>)</option>
            <?php endforeach; ?>
          </select>

          <label for="fecha">Fecha *</label>
            <input type="date" id="fecha" name="fecha" value="<?= htmlspecialchars(isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d')) ?>" min="<?= date('Y-m-d') ?>">

          <label for="franja">Franja horaria *</label>
            <!-- Selector: Franjas horarias de 2 horas (08:00 - 23:59) -->
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

          <!-- Campos ocultos: Hora inicio/fin (se rellenan desde JS al seleccionar franja) -->
          <input type="hidden" id="horaInicio" name="horaInicio" value="">
          <input type="hidden" id="horaFin" name="horaFin" value="">

          <div style="margin-top:12px;">
            <button class="btn-primary" type="submit">Reservar</button>
            <a class="btn-ghost" href="selecciona_sala.php">Cancelar</a>
          </div>
        </form>
      </div>
    </section>

    <!-- Sección: Listado de mesas con sus reservas -->
    <section class="mesas-container admin-salas-section">
      <h2>Mesas</h2>
      <div class="mesas-list">
        <h3>Reservas para el día</h3>
        <!-- Formulario: Filtros acumulativos (fecha + franja + sillas mínimas) -->
        <form method="get" action="reservas.php" id="form-buscar-fecha" class="form-filtros">
          <div id="buscarErrors" class="form-errors" style="color:#a00;margin-bottom:8px;"></div>
          <input type="hidden" name="idSala" value="<?= intval($selectedSala) ?>">
          <div class="filters-row">
          <div class="filter-group">
            <label for="fecha_buscar">Fecha:</label>
            <input type="date" id="fecha_buscar" name="fecha" value="<?= htmlspecialchars($selectedDate) ?>" min="<?= date('Y-m-d') ?>">
          </div>
          <div class="filter-group">
            <label for="franja_buscar" class="ml-8">Franja:</label>
            <select id="franja_buscar" name="franja">
            <?php $selectedFranja = isset($_GET['franja']) ? $_GET['franja'] : ''; ?>
            <option value="" <?= $selectedFranja === '' ? 'selected' : '' ?>>Todo el día</option>
            <option value="08:00-10:00" <?= $selectedFranja === '08:00-10:00' ? 'selected' : '' ?>>08:00 - 10:00</option>
            <option value="10:00-12:00" <?= $selectedFranja === '10:00-12:00' ? 'selected' : '' ?>>10:00 - 12:00</option>
            <option value="12:00-14:00" <?= $selectedFranja === '12:00-14:00' ? 'selected' : '' ?>>12:00 - 14:00</option>
            <option value="14:00-16:00" <?= $selectedFranja === '14:00-16:00' ? 'selected' : '' ?>>14:00 - 16:00</option>
            <option value="16:00-18:00" <?= $selectedFranja === '16:00-18:00' ? 'selected' : '' ?>>16:00 - 18:00</option>
            <option value="18:00-20:00" <?= $selectedFranja === '18:00-20:00' ? 'selected' : '' ?>>18:00 - 20:00</option>
            <option value="20:00-22:00" <?= $selectedFranja === '20:00-22:00' ? 'selected' : '' ?>>20:00 - 22:00</option>
            <option value="22:00-23:59" <?= $selectedFranja === '22:00-23:59' ? 'selected' : '' ?>>22:00 - 23:59</option>
            </select>
          </div>
          <div class="filter-group">
            <label for="sillas_buscar" class="ml-8">Sillas mín.:</label>
            <?php $selectedSillas = isset($_GET['sillas']) ? intval($_GET['sillas']) : 0; ?>
            <input type="number" id="sillas_buscar" name="sillas" value="<?= $selectedSillas ?>" min="0" max="20" class="input-sillas">
          </div>
          <div class="filters-actions">
            <button class="btn btn-primary" type="submit">Ver</button>
            <a class="btn btn-ghost" href="reservas.php?idSala=<?= intval($selectedSala) ?>">Limpiar filtros</a>
          </div>
          </div>
        </form>

        <?php
        // Agrupar reservas por idMesa para mostrarlas organizadas en cada fila
        $reservasByMesa = [];
        if (!empty($reservas)) {
            foreach ($reservas as $r) {
                $reservasByMesa[$r['idMesa']][] = $r;
            }
        }
        ?>

        <?php if (!empty($mesas)): ?>
          <!-- Tabla: Mesas con sus reservas del día filtrado -->
          <table class="admin-table" style="margin-top:10px;width:100%;">
            <thead><tr><th>Mesa</th><th>Disponibilidad / Reservas</th><th>Acciones</th></tr></thead>
            <tbody>
            <?php foreach ($mesas as $m): ?>
              <?php 
                 $mReservas = $reservasByMesa[$m['idMesa']] ?? [];
              ?>
              <tr>
                <td>
                    <strong><?= htmlspecialchars($m['nombre']) ?></strong>
                    <br>
                    <small style="color: #666;"><?= htmlspecialchars(ucfirst($m['estado'])) ?></small>
                </td>
                <td>
                   <?php if (empty($mReservas)): ?>
                       <span class="status-disponible">Disponible todo el día</span>
                   <?php else: ?>
                       <ul class="lista-reservas">
                       <?php foreach ($mReservas as $r): ?>
                           <li class="item-reserva">
                               <div class="reserva-info">
                                   <strong><?= htmlspecialchars($r['horaInicio']) ?> - <?= htmlspecialchars($r['horaFin']) ?></strong>
                                   <div class="reserva-detalles">
                                      <?php if (!empty($r['idUsuario'])): ?>
                                        Reservado por: <?= htmlspecialchars(($r['usuarioNombre'] ?? '') . ' ' . ($r['usuarioApellidos'] ?? '')) ?> (Usuario)
                                      <?php elseif (!empty($r['idCamarero'])): ?>
                                        Reservado por: <?= htmlspecialchars(($r['camNombre'] ?? '') . ' ' . ($r['camApellidos'] ?? '')) ?> (Camarero)
                                      <?php else: ?>
                                        Reservado
                                      <?php endif; ?>
                                   </div>
                               </div>
                               
                               <?php
                                    // Verificar si el usuario actual puede cancelar la reserva
                                    $idCamareroSess = $_SESSION['idCamarero'] ?? null;
                                    $idUsuarioSess = $_SESSION['idUsuario'] ?? null;
                                    $rol = $_SESSION['rol'] ?? null;
                                    $isOwner = false;
                                    if ($rol === 'admin') {
                                        $isOwner = true;
                                    } else {
                                        if ($idCamareroSess && $r['idCamarero'] == $idCamareroSess) $isOwner = true;
                                        if ($idUsuarioSess && $r['idUsuario'] == $idUsuarioSess) $isOwner = true;
                                    }
                               ?>
                               <?php if ($isOwner): ?>
                                 <div class="reserva-acciones">
                                   <a class="btn-danger js-delete" data-confirm="¿Estás seguro de que deseas cancelar esta reserva?" href="../processes/admin/reserva/reserva_delete.php?id=<?= intval($r['idReserva']) ?>">Cancelar</a>
                                 </div>
                               <?php endif; ?>
                           </li>
                       <?php endforeach; ?>
                       </ul>
                   <?php endif; ?>
                </td>
                <td>
                    <button type="button" class="btn btn-reservar-mesa" style="font-size:0.9em;" data-idmesa="<?= $m['idMesa'] ?>">Reservar</button>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        <?php else: ?>
          <p>No hay mesas en esta sala o no se ha seleccionado ninguna.</p>
        <?php endif; ?>
      </div>
    </section>
  </main>

<script src="../js/script.js"></script>
</body>
</html>
