<?php
session_start();
include '../database/conexion.php';

if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], ['admin', 'camarero'])) {
    header("Location: login.php");
    exit();
}

$stmt = $conn->query("SELECT * FROM usuario ORDER BY fechaContratacion DESC");
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

$roles_disponibles = ['admin', 'gerent', 'camarero', 'manteniment', 'caixa'];
$estados_disponibles = ['activo', 'inactivo'];

$error_msg = "";
$success_msg = "";

if (isset($_SESSION['error_usuarios'])) {
    $error_msg = $_SESSION['error_usuarios'];
    unset($_SESSION['error_usuarios']);
}

if (isset($_SESSION['success_usuarios'])) {
    $success_msg = $_SESSION['success_usuarios'];
    unset($_SESSION['success_usuarios']);
}

$accion = isset($_GET['accion']) ? $_GET['accion'] : 'listar';
$editar_id = isset($_GET['id']) ? $_GET['id'] : null;

$usuario_editar = null;
if ($accion == 'editar' && $editar_id) {
    $stmt = $conn->prepare("SELECT * FROM usuario WHERE idUsuario = ?");
    $stmt->execute([$editar_id]);
    $usuario_editar = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Administración de Usuarios</title>
        <link rel="stylesheet" href="../styles/estilos.css">
</head>
<body class="page-sala">
    <header class="admin-header">
        <h1>Administración</h1>
        <div class="toolbar">
            <a class="btn-primary" href="admin_usuarios.php">Admin Usuarios</a>
            <a class="btn-primary" href="admin_salas.php">Admin Salas</a>
            <a class="btn-primary" href="admin_mesas.php">Admin Mesas</a>
            <form id="cerrar-sesion" action="../processes/logout.php" method="post">
                <button type="submit" class="btn-ghost">Cerrar sesión</button>
            </form>
        </div>
    </header>

    <main class="contenedor-principal admin-grid">
        <section class="info-sala">
            <?php if ($accion == 'crear' || $accion == 'editar'): ?>
                <h2><?= $accion == 'crear' ? 'Crear Nuevo Usuario' : 'Editar Usuario' ?></h2>
                <div class="white-box">
                    <?php if (!empty($error_msg)): ?>
                        <div class="alert-error"><?php echo $error_msg; ?></div>
                    <?php endif; ?>
                    <?php if (!empty($success_msg)): ?>
                        <div class="alert-success"><?php echo $success_msg; ?></div>
                    <?php endif; ?>

                    <?php if ($accion == 'crear'): ?>
                        <form method="POST" action="../processes/usuario_create.php">
                    <?php else: ?>
                        <form method="POST" action="../processes/usuario_update.php">
                            <input type="hidden" name="idUsuario" value="<?php echo $usuario_editar['idUsuario']; ?>">
                    <?php endif; ?>

                            <label for="nombre">Nombre *</label>
                            <input type="text" id="nombre" name="nombre" required value="<?= $accion=='editar' ? htmlspecialchars($usuario_editar['nombre']) : '' ?>">

                            <label for="apellidos">Apellidos *</label>
                            <input type="text" id="apellidos" name="apellidos" required value="<?= $accion=='editar' ? htmlspecialchars($usuario_editar['apellidos']) : '' ?>">

                            <label for="nombreUsu">Nombre de Usuario *</label>
                            <input type="text" id="nombreUsu" name="nombreUsu" required value="<?= $accion=='editar' ? htmlspecialchars($usuario_editar['nombreUsu']) : '' ?>">

                            <label for="email">Email *</label>
                            <input type="email" id="email" name="email" required value="<?= $accion=='editar' ? htmlspecialchars($usuario_editar['email']) : '' ?>">

                            <label for="dni">DNI/NIF *</label>
                            <input type="text" id="dni" name="dni" required value="<?= $accion=='editar' ? htmlspecialchars($usuario_editar['dni']) : '' ?>">

                            <label for="telefono">Teléfono *</label>
                            <input type="tel" id="telefono" name="telefono" required value="<?= $accion=='editar' ? htmlspecialchars($usuario_editar['telefono']) : '' ?>">

                            <label for="fechaContratacion">Fecha Contratación *</label>
                            <input type="date" id="fechaContratacion" name="fechaContratacion" required value="<?= $accion=='editar' ? $usuario_editar['fechaContratacion'] : '' ?>">

                            <label for="rol">Rol *</label>
                            <select id="rol" name="rol" required>
                                    <?php foreach ($roles_disponibles as $rol): ?>
                                            <option value="<?php echo $rol; ?>" <?= ($accion=='editar' && $usuario_editar['rol']==$rol) ? 'selected' : '' ?>><?php echo ucfirst($rol); ?></option>
                                    <?php endforeach; ?>
                            </select>

                            <label for="estado">Estado *</label>
                            <select id="estado" name="estado" required>
                                    <?php foreach ($estados_disponibles as $estado): ?>
                                            <option value="<?php echo $estado; ?>" <?= ($accion=='editar' && $usuario_editar['estado']==$estado) ? 'selected' : '' ?>><?php echo ucfirst($estado); ?></option>
                                    <?php endforeach; ?>
                            </select>

                            <label for="password"><?= $accion=='crear' ? 'Contraseña * (mínimo 6 caracteres)' : 'Cambiar Contraseña (opcional)' ?></label>
                            <input type="password" id="password" name="password" <?= $accion=='crear' ? 'required' : '' ?> placeholder="<?= $accion=='editar' ? 'Nueva contraseña (opcional)' : '' ?>">

                            <?php if ($accion == 'crear'): ?>
                                <label for="password_confirm">Confirmar Contraseña *</label>
                                <input type="password" id="password_confirm" name="password_confirm" required>
                            <?php endif; ?>

                            <div style="margin-top:12px;">
                                <button class="btn-primary" type="submit"><?= $accion=='crear' ? 'Crear Usuario' : 'Guardar Cambios' ?></button>
                                <a class="btn-ghost" href="admin_usuarios.php">Cancelar</a>
                            </div>
                        </form>
                </div>
            <?php else: ?>
                <h2>Usuarios</h2>
                <?php if (!empty($error_msg)): ?>
                        <div class="alert-error"><?php echo $error_msg; ?></div>
                <?php endif; ?>
                <?php if (!empty($success_msg)): ?>
                        <div class="alert-success"><?php echo $success_msg; ?></div>
                <?php endif; ?>
                    <div style="margin-top:10px;">
                    <a class="btn-primary" href="admin_usuarios.php?accion=crear">+ Nuevo Usuario</a>
                    <a class="btn-ghost" href="admin_salas.php">Volver a Salas</a>
                </div>
            <?php endif; ?>
        </section>

        <section class="mesas-container admin-salas-section">
            <h2>Listado de Usuarios</h2>
            <div class="mesas-table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr><th>Nombre</th><th>Usuario</th><th>Email</th><th>Rol</th><th>Estado</th><th>Contratación</th><th>Acciones</th></tr>
                    </thead>
                    <tbody>
                        <?php if (count($usuarios) > 0): ?>
                            <?php foreach ($usuarios as $u): ?>
                                <tr>
                                    <td><?= htmlspecialchars($u['nombre'] . ' ' . $u['apellidos']); ?></td>
                                    <td><?= htmlspecialchars($u['nombreUsu']); ?></td>
                                    <td><?= htmlspecialchars($u['email']); ?></td>
                                    <td><?= ucfirst($u['rol']); ?></td>
                                    <td><?= ucfirst($u['estado']); ?></td>
                                    <td><?= date('d/m/Y', strtotime($u['fechaContratacion'])); ?></td>
                                    <td>
                                        <a class="btn-primary" href="admin_usuarios.php?accion=editar&id=<?php echo $u['idUsuario']; ?>">Editar</a>
                                        <a class="btn-danger js-delete" data-confirm="Eliminar usuario?" href="../processes/usuario_delete.php?id=<?php echo $u['idUsuario']; ?>">Eliminar</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="no-users">No hay usuarios registrados. <a href="admin_usuarios.php?accion=crear" class="muted-link">Crear nuevo usuario</a></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

  
</body>
</html>
