<?php
require_once '../../processes/admin/admin_mesa/admin_mesas_logic.php';
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Administrar Mesas</title>
  <link rel="stylesheet" href="../../styles/estilos.css">
  <link rel="stylesheet" href="../../styles/responsive.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="../../js/script.js"></script>
</head>
<body class="page-sala">
    <?php if (isset($access_denied) && $access_denied): ?>
        <input type="hidden" id="access-denied-msg" value="<?= htmlspecialchars($denied_message) ?>">
        <script src="../../js/script.js"></script>
        </body></html>
        <?php exit; ?>
    <?php endif; ?>
    <header class="admin-header">
    <h1>Administrar Mesas</h1>
    <div class="toolbar">
      <a class="btn-primary" href="admin_salas.php">Admin Salas</a>
      <a class="btn-ghost" href="admin_salas.php">Volver a Salas</a>
      <form id="cerrar-sesion" action="../../processes/logout.php" method="post">
        <button type="submit" class="btn-ghost">Cerrar sesión</button>
      </form>
    </div>
  </header>
  <main class="contenedor-principal admin-grid">
    <section class="info-sala">
      <h2>Crear / Editar Mesa</h2>
      <form action="../../processes/admin/admin_mesa/mesa_create.php" method="post">
        <input type="hidden" name="idMesa" value="<?= $editMesa ? intval($editMesa['idMesa']) : '' ?>">
        <label>Nombre</label>
        <input type="text" id="nombreMesa" name="nombre" value="<?= $editMesa ? htmlspecialchars($editMesa['nombre']) : '' ?>">
        <div id="nombreMesaError" class="error-message" style="color:red;font-size:0.9em;margin-top:4px;"></div>

        <label>Nº Sillas</label>
        <input type="number" id="numSillas" name="numSillas" min="1" value="<?= $editMesa ? intval($editMesa['numSillas']) : 2 ?>">
        <div id="numSillasError" class="error-message" style="color:red;font-size:0.9em;margin-top:4px;"></div>

        <label>Sala</label>
        <select name="idSala">
          <?php foreach($salas as $s): ?>
            <option value="<?= intval($s['idSala']) ?>" <?= ($editMesa && $editMesa['idSala']==$s['idSala']) || (!$editMesa && $idSala==$s['idSala']) ? 'selected' : '' ?>><?= htmlspecialchars($s['nombre']) ?></option>
          <?php endforeach; ?>
        </select> 

        <label>Estado</label>
        <select name="estado">
          <option value="libre" <?= $editMesa && $editMesa['estado']=='libre' ? 'selected' : '' ?>>libre</option>
          <option value="ocupada" <?= $editMesa && $editMesa['estado']=='ocupada' ? 'selected' : '' ?>>ocupada</option>
        </select>

        <div class="form-actions">
          <button class="btn" type="submit"><?= $editMesa ? 'Actualizar' : 'Crear' ?></button>
          <?php if($editMesa): ?> <a class="btn" href="admin_mesas.php?idSala=<?= intval($editMesa['idSala']) ?>">Cancelar</a><?php endif; ?>
        </div>
      </form>
    </section>

    <section class="mesas-container">
      <h2>Mesas de la sala <?= $idSala ?></h2>
      <div class="selector-salas admin-selector" role="tablist" aria-label="Selector de salas">
        <?php foreach($salas as $s):
            $isActive = ($idSala == intval($s['idSala']));
        ?>
          <a href="?idSala=<?= intval($s['idSala']) ?>" class="selector-sala-pill <?= $isActive ? 'active' : '' ?>" data-id="<?= intval($s['idSala']) ?>">
            <?= htmlspecialchars($s['nombre']) ?>
          </a>
        <?php endforeach; ?>
      </div>
      <div class="mesas-table-wrapper">
        <table class="table-historial">
          <thead><tr><th>ID</th><th>Nombre</th><th>#Sillas</th><th>Estado</th><th>Acciones</th></tr></thead>
          <tbody>
          <?php foreach($mesas as $m): ?>
            <tr>
              <td data-label="ID"><?= intval($m['idMesa']) ?></td>
              <td data-label="Nombre"><?= htmlspecialchars($m['nombre']) ?></td>
              <td data-label="#Sillas"><?= intval($m['numSillas']) ?></td>
              <td data-label="Estado"><?= htmlspecialchars($m['estado']) ?></td>
              <td data-label="Acciones">
                <div class="action-buttons">
                  <a class="btn" href="?edit=<?= intval($m['idMesa']) ?>&idSala=<?= intval($idSala) ?>">Editar</a>
                  <a class="btn js-delete" data-confirm="Eliminar mesa?" href="../../processes/admin/admin_mesa/mesa_delete.php?id=<?= intval($m['idMesa']) ?>&idSala=<?= intval($idSala) ?>">Eliminar</a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>
  </main>
</body>
</html>
