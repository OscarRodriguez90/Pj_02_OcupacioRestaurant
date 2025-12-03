<?php
session_start();
include '../database/conexion.php';

// if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
//     header("Location: login.php");
//     exit();
// }

if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], ['admin','camarero'])) {
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
<body>
    <div class="admin-container">
        <h1>Administración de Usuarios</h1>
        <?php if ($accion == 'listar'): ?>
            <a href="admin_usuarios.php?accion=crear" class="btn-new">+ Nuevo Usuario</a>
            <a href="admin_mesas.php" class="btn-back">Volver</a>
        <?php else: ?>
            <a href="admin_usuarios.php" class="btn-back">Volver a Listado</a>
        <?php endif; ?>

        <?php if (!empty($error_msg)): ?>
            <div class="alert-error"><?php echo $error_msg; ?></div>
        <?php endif; ?>
        <?php if (!empty($success_msg)): ?>
            <div class="alert-success"><?php echo $success_msg; ?></div>
        <?php endif; ?>

        <?php if ($accion == 'listar'): ?>
            <div class="white-box">
                <?php if (count($usuarios) > 0): ?>
                    <table class="admin-table">
                        <tr class="admin-table-header">
                            <th>Nombre</th>
                            <th>Usuario</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th>Contratación</th>
                            <th>Acciones</th>
                        </tr>
                        <?php foreach ($usuarios as $u): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($u['nombre'] . ' ' . $u['apellidos']); ?></td>
                                <td><?php echo htmlspecialchars($u['nombreUsu']); ?></td>
                                <td><?php echo htmlspecialchars($u['email']); ?></td>
                                <td><strong><?php echo ucfirst($u['rol']); ?></strong></td>
                                <td><strong><?php echo ucfirst($u['estado']); ?></strong></td>
                                <td><?php echo date('d/m/Y', strtotime($u['fechaContratacion'])); ?></td>
                                <td>
                                    <a href="admin_usuarios.php?accion=editar&id=<?php echo $u['idUsuario']; ?>" class="btn-edit">Editar</a>
                                    <a href="javascript:void(0);" onclick="confirmarEliminar(<?php echo $u['idUsuario']; ?>)" class="btn-delete">Eliminar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="no-users">No hay usuarios registrados. <a href="admin_usuarios.php?accion=crear" class="muted-link">Crear nuevo usuario</a></td></tr>
                <?php endif; ?>
                    </table>
            </div>

        <?php elseif ($accion == 'crear'): ?>
            <div style="background: white; border-radius: 4px; padding: 20px; margin-top: 15px;">
                <h2>Crear Nuevo Usuario</h2>
                <form method="POST" action="../processes/usuario_create.php">
                    <label for="nombre">Nombre *</label>
                    <input type="text" id="nombre" name="nombre" required>
                    
                    <label for="apellidos">Apellidos *</label>
                    <input type="text" id="apellidos" name="apellidos" required>
                    
                    <label for="nombreUsu">Nombre de Usuario *</label>
                    <input type="text" id="nombreUsu" name="nombreUsu" required>
                    
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required>
                    
                    <label for="dni">DNI/NIF *</label>
                    <input type="text" id="dni" name="dni" required>
                    
                    <label for="telefono">Teléfono *</label>
                    <input type="tel" id="telefono" name="telefono" required>
                    
                    <label for="fechaContratacion">Fecha Contratación *</label>
                    <input type="date" id="fechaContratacion" name="fechaContratacion" required>
                    
                    <label for="rol">Rol * (Selecciona el rol del usuario)</label>
                    <select id="rol" name="rol" required>
                        <option value="">Seleccionar rol...</option>
                        <?php foreach ($roles_disponibles as $rol): ?>
                            <option value="<?php echo $rol; ?>"><?php echo ucfirst($rol); ?></option>
                        <?php endforeach; ?>
                    </select>
                    
                    <label for="password">Contraseña * (mínimo 6 caracteres)</label>
                    <input type="password" id="password" name="password" required>
                    
                    <label for="password_confirm">Confirmar Contraseña *</label>
                    <input type="password" id="password_confirm" name="password_confirm" required>
                    
                    <button type="submit">Crear Usuario</button>
                    <a href="admin_usuarios.php" class="btn btn-back">Cancelar</a>
                </form>
            </div>

        <?php elseif ($accion == 'editar' && $usuario_editar): ?>
            <div style="background: white; border-radius: 4px; padding: 20px; margin-top: 15px;">
                <h2>Editar Usuario</h2>
                <form method="POST" action="../processes/usuario_update.php">
                    <input type="hidden" name="idUsuario" value="<?php echo $usuario_editar['idUsuario']; ?>">

                    <label for="nombre">Nombre *</label>
                    <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($usuario_editar['nombre']); ?>" required>
                    
                    <label for="apellidos">Apellidos *</label>
                    <input type="text" id="apellidos" name="apellidos" value="<?php echo htmlspecialchars($usuario_editar['apellidos']); ?>" required>
                    
                    <label for="nombreUsu">Nombre de Usuario *</label>
                    <input type="text" id="nombreUsu" name="nombreUsu" value="<?php echo htmlspecialchars($usuario_editar['nombreUsu']); ?>" required>
                    
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($usuario_editar['email']); ?>" required>
                    
                    <label for="dni">DNI/NIF *</label>
                    <input type="text" id="dni" name="dni" value="<?php echo htmlspecialchars($usuario_editar['dni']); ?>" required>
                    
                    <label for="telefono">Teléfono *</label>
                    <input type="tel" id="telefono" name="telefono" value="<?php echo htmlspecialchars($usuario_editar['telefono']); ?>" required>
                    
                    <label for="fechaContratacion">Fecha Contratación *</label>
                    <input type="date" id="fechaContratacion" name="fechaContratacion" value="<?php echo $usuario_editar['fechaContratacion']; ?>" required>
                    
                    <label for="rol">Rol *</label>
                    <select id="rol" name="rol" required>
                        <?php foreach ($roles_disponibles as $rol): ?>
                            <option value="<?php echo $rol; ?>" <?php echo $usuario_editar['rol'] == $rol ? 'selected' : ''; ?>><?php echo ucfirst($rol); ?></option>
                        <?php endforeach; ?>
                    </select>
                    
                    <label for="estado">Estado *</label>
                    <select id="estado" name="estado" required>
                        <?php foreach ($estados_disponibles as $estado): ?>
                            <option value="<?php echo $estado; ?>" <?php echo $usuario_editar['estado'] == $estado ? 'selected' : ''; ?>><?php echo ucfirst($estado); ?></option>
                        <?php endforeach; ?>
                    </select>
                    
                    <label for="password">Cambiar Contraseña (opcional, dejar vacío para mantener actual)</label>
                    <input type="password" id="password" name="password" placeholder="Nueva contraseña (opcional)">
                    
                    <button type="submit">Guardar Cambios</button>
                    <a href="admin_usuarios.php" class="btn btn-back">Cancelar</a>
                </form>
            </div>
        <?php endif; ?>
    </div>

    
</body>
</html>
