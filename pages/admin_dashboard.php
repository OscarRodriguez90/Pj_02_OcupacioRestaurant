<?php
require_once '../processes/admin_dashboard_logic.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administración</title>
    <link rel="stylesheet" href="../styles/estilos.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="dashboard">
    <?php if (isset($access_denied) && $access_denied): ?>
        <input type="hidden" id="access-denied-msg" value="<?= htmlspecialchars($denied_message) ?>">
        <script src="../js/script.js"></script>
        </body></html>
        <?php exit; ?>
    <?php endif; ?>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <h1>👨‍💼 Panel de Administración</h1>
                    <p>Sistema de Gestión - Restaurant</p>
                </div>
                <div style="text-align: right;">
                    <div class="user-info">
                        👤 <?php echo htmlspecialchars($_SESSION['username']); ?>
                    </div>
                    <a href="../processes/logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
        </div>

        <!-- MENÚ PRINCIPAL -->
        <div class="admin-menu">
            <?php if (in_array($_SESSION['rol'], ['admin', 'gerent'])): ?>
            <a href="admin_usuarios.php" class="menu-card">
                <div class="menu-icon">👥</div>
                <div class="menu-title">Gestión de Usuários</div>
                <div class="menu-desc">CRUD de usuarios, rols y permisos</div>
            </a>
            <?php endif; ?>

            <?php if (in_array($_SESSION['rol'], ['admin', 'manteniment'])): ?>
            <a href="admin_mesas.php" class="menu-card">
                <div class="menu-icon">🪑</div>
                <div class="menu-title">Gestión de Mesas</div>
                <div class="menu-desc">Crear, editar y eliminar mesas</div>
            </a>

            <a href="admin_salas.php" class="menu-card">
                <div class="menu-icon">🏛️</div>
                <div class="menu-title">Gestión de Salas</div>
                <div class="menu-desc">Administrar salas del restaurant</div>
            </a>
            <?php endif; ?>

            <?php if ($_SESSION['rol'] === 'admin'): ?>
            <a href="historial_general.php" class="menu-card">
                <div class="menu-icon">📊</div>
                <div class="menu-title">Historial General</div>
                <div class="menu-desc">Reporte de ocupaciones totales</div>
            </a>
            <?php endif; ?>
        </div>

        <!-- ESTADÍSTICAS -->
        <div class="dashboard-grid">
            <!-- Card: Total Usuários -->
            <?php if (in_array($_SESSION['rol'], ['admin', 'gerent'])): ?>
            <div class="dashboard-card">
                <div class="card-header">
                    <div class="card-title">Total de Usuários</div>
                    <div class="card-icon">👥</div>
                </div>
                <div class="card-value"><?php echo $total_usuarios; ?></div>
                <div class="card-label">Registrados en el sistema</div>
                <div class="stats-row">
                    <div class="stat-item">
                        <div class="stat-label">Activos</div>
                        <div class="stat-value" style="color: #28a745;"><?php echo $usuarios_activos; ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Inactivos</div>
                        <div class="stat-value" style="color: #dc3545;"><?php echo $usuarios_inactivos; ?></div>
                    </div>
                </div>
                <a href="admin_usuarios.php" class="card-link" style="margin-top: 15px;">Gestionar →</a>
            </div>
            <?php endif; ?>

            <!-- Card: Salas -->
            <?php if (in_array($_SESSION['rol'], ['admin', 'manteniment'])): ?>
            <div class="dashboard-card">
                <div class="card-header">
                    <div class="card-title">Salas</div>
                    <div class="card-icon">🏛️</div>
                </div>
                <div class="card-value"><?php echo $total_salas; ?></div>
                <div class="card-label">Salas disponibles</div>
                <a href="admin_salas.php" class="card-link" style="margin-top: 30px;">Gestionar →</a>
            </div>

            <!-- Card: Mesas -->
            <div class="dashboard-card">
                <div class="card-header">
                    <div class="card-title">Mesas</div>
                    <div class="card-icon">🪑</div>
                </div>
                <div class="card-value"><?php echo $total_mesas; ?></div>
                <div class="card-label">Mesas registradas</div>
                <a href="admin_mesas.php" class="card-link" style="margin-top: 30px;">Gestionar →</a>
            </div>
            <?php endif; ?>

            <!-- Card: Ocupaciones Hoy -->
             <?php if ($_SESSION['rol'] === 'admin'): ?>
            <div class="dashboard-card">
                <div class="card-header">
                    <div class="card-title">Ocupaciones Hoy</div>
                    <div class="card-icon">📅</div>
                </div>
                <div class="card-value"><?php echo $ocupaciones_hoy; ?></div>
                <div class="card-label">Mesas ocupadas hoy</div>
                <a href="historial_general.php" class="card-link" style="margin-top: 20px;">Ver historial →</a>
            </div>
            <?php endif; ?>
        </div>

        <!-- DISTRIBUCIÓN DE ROLES -->
        <div class="chart-container">
            <div class="chart-title">📊 Distribución de Usuários por Rol</div>
            <div class="rol-distribution">
                <?php 
                $total = array_sum(array_column($usuarios_por_rol, 'count'));
                foreach ($usuarios_por_rol as $rol_data):
                    $percentage = $total > 0 ? ($rol_data['count'] / $total) * 100 : 0;
                    $rol_class = 'rol-' . $rol_data['rol'];
                ?>
                    <div class="rol-item">
                        <div class="rol-label"><?php echo ucfirst($rol_data['rol']); ?></div>
                        <div class="rol-bar">
                            <div class="rol-fill <?php echo $rol_class; ?>" style="width: <?php echo $percentage; ?>%;">
                                <?php echo $rol_data['count']; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- INFORMACIÓN ÚTIL -->
        <div class="dashboard-card" style="background-color: #f0f4ff; border-left: 4px solid #667eea;">
            <div class="card-title" style="color: #667eea; margin-bottom: 15px;">ℹ️ Información del Sistema</div>
            <ul style="margin: 0; padding: 0 0 0 20px; color: #666; line-height: 1.8;">
                <li>Versión: 1.0.0</li>
                <li>Base de datos: bd_pokefullStack</li>
                <li>Tabla de usuários: usuario</li>
                <li>Sistema: CRUD Completo (Create, Read, Update, Delete)</li>
                <li>Contraseñas: Encriptadas con bcrypt</li>
                <li>Última sincronización: <?php echo date('d/m/Y H:i:s'); ?></li>
            </ul>
        </div>

        <div class="footer">
            <p>© 2024 Sistema de Gestión de Restaurant - Todos los derechos reservados</p>
        </div>
    </div>
</body>
</html>
