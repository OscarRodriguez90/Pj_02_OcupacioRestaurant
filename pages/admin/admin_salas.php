<?php
require_once '../../processes/admin/admin_sala/admin_salas_logic.php';
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Administrar Salas</title>
  <link rel="stylesheet" href="../../styles/estilos.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="page-sala">
    <?php if (isset($access_denied) && $access_denied): ?>
        <input type="hidden" id="access-denied-msg" value="<?= htmlspecialchars($denied_message) ?>">
        <script src="../../js/script.js"></script>
        </body></html>
        <?php exit; ?>
    <?php endif; ?>
    <header class="admin-header">
    <h1>Administrar Salas</h1>
    <div class="toolbar">
      <?php if($_SESSION['rol'] === 'admin'): ?>
      <a class="btn-primary" href="admin_usuarios.php">Admin Usuarios</a>
      <?php endif; ?>
      <a class="btn-primary" href="admin_mesas.php">Admin Mesas</a>
      <a class="btn-ghost" href="../selecciona_sala.php">Volver a selección</a>
      <form id="cerrar-sesion" action="../../processes/logout.php" method="post">
        <button type="submit" class="btn-ghost">Cerrar sesión</button>
      </form>
    </div>
  </header>
  <main class="contenedor-principal admin-grid">
    <?php if($msg): ?><p style="color:red"><?=htmlspecialchars($msg)?></p><?php endif; ?>

    <section class="info-sala">
      <h2>Crear / Editar Sala</h2>
      <form action="../../processes/admin/admin_sala/sala_create.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="idSala" value="<?= $editSala ? intval($editSala['idSala']) : '' ?>">
        <label>Imagen de fondo</label>
        <input type="file" id="fondoSala" name="fondo" accept="image/*">
        <div id="fondoSalaError" class="error-message" style="color:red;font-size:0.9em;margin-top:4px;"></div>
        <label>Nombre</label>
        <input type="text" id="nombreSala" name="nombre" value="<?= $editSala ? htmlspecialchars($editSala['nombre']) : '' ?>">
        <div id="nombreSalaError" class="error-message" style="color:red;font-size:0.9em;margin-top:4px;"></div>
        <div style="margin-top:10px;">
          <button class="btn-primary" type="submit"><?= $editSala ? 'Actualizar' : 'Crear' ?></button>
          <?php if($editSala): ?> <a class="btn-ghost" href="admin_salas.php">Cancelar</a><?php endif; ?>
        </div>
      </form>
    </section>

    <section class="mesas-container admin-salas-section">
      <h2>Listado de Salas</h2>
      <table class="salas-table">
        <thead>
          <tr><th>ID</th><th>Nombre</th><th>Acciones</th></tr>
        </thead>
        <tbody>
        <?php foreach($salas as $s): ?>
          <tr>
            <td><?= intval($s['idSala']) ?></td>
            <td><?= htmlspecialchars($s['nombre']) ?></td>
            <td>
              <a class="btn-primary" href="?edit=<?= intval($s['idSala']) ?>">Editar</a>
              <a class="btn-danger js-delete" data-confirm="Eliminar sala?" href="../../processes/admin/admin_sala/sala_delete.php?id=<?= intval($s['idSala']) ?>">Eliminar</a>
              <a class="btn-ghost" href="admin_mesas.php?idSala=<?= intval($s['idSala']) ?>">Ver mesas</a>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </section>
  </main>
  <script src="../../js/script.js"></script>
</body>
</html>
