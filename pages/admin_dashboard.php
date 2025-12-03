<?php
session_start();
include '../database/conexion.php';

// Verificar que l'usuari sigui admin
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Obtenir estadístiques
$stmt = $conn->query("SELECT COUNT(*) FROM usuario");
$total_usuarios = $stmt->fetchColumn();

$stmt = $conn->query("SELECT COUNT(*) FROM usuario WHERE estado = 'activo'");
$usuarios_activos = $stmt->fetchColumn();

$stmt = $conn->query("SELECT COUNT(*) FROM usuario WHERE estado = 'inactivo'");
$usuarios_inactivos = $stmt->fetchColumn();

$stmt = $conn->query("SELECT rol, COUNT(*) as count FROM usuario GROUP BY rol");
$usuarios_por_rol = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->query("SELECT COUNT(*) FROM sala");
$total_salas = $stmt->fetchColumn();

$stmt = $conn->query("SELECT COUNT(*) FROM mesa");
$total_mesas = $stmt->fetchColumn();

$stmt = $conn->query("SELECT COUNT(*) FROM historico WHERE DATE(horaDesocupacion) = CURDATE()");
$ocupaciones_hoy = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administración</title>
    <link rel="stylesheet" href="../styles/estilos.css">
    <style>
        .dashboard {
            min-height: 100vh;
            background-color: #f5f5f5;
            padding: 20px;
        }

        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .dashboard-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .dashboard-header h1 {
            margin: 0 0 10px 0;
            font-size: 32px;
        }

        .dashboard-header p {
            margin: 0;
            opacity: 0.9;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 15px;
        }

        .card-title {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }

        .card-icon {
            font-size: 28px;
            opacity: 0.5;
        }

        .card-value {
            font-size: 36px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 10px;
        }

        .card-label {
            color: #666;
            font-size: 14px;
            margin-bottom: 15px;
        }

        .card-link {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
            font-weight: bold;
            display: inline-block;
            transition: color 0.3s;
        }

        .card-link:hover {
            color: #764ba2;
        }

        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }

        .stat-item {
            background-color: #f9f9f9;
            padding: 10px;
            border-radius: 5px;
            border-left: 3px solid #667eea;
        }

        .stat-label {
            font-size: 12px;
            color: #999;
            text-transform: uppercase;
        }

        .stat-value {
            font-size: 20px;
            font-weight: bold;
            color: #333;
        }

        .admin-menu {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }

        .menu-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
            text-decoration: none;
            border: 2px solid transparent;
        }

        .menu-card:hover {
            transform: translateY(-5px);
            border-color: #667eea;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .menu-icon {
            font-size: 40px;
            margin-bottom: 10px;
        }

        .menu-title {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }

        .menu-desc {
            font-size: 12px;
            color: #999;
        }

        .chart-container {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .chart-title {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-bottom: 15px;
        }

        .rol-distribution {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .rol-item {
            display: flex;
            align-items: center;
            gap: 10px;
            flex: 1;
            min-width: 150px;
        }

        .rol-bar {
            flex: 1;
            height: 30px;
            background-color: #e0e0e0;
            border-radius: 5px;
            overflow: hidden;
            position: relative;
        }

        .rol-fill {
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding-right: 10px;
            color: white;
            font-weight: bold;
            font-size: 12px;
        }

        .rol-admin { background-color: #dc3545; }
        .rol-gerent { background-color: #fd7e14; }
        .rol-camarero { background-color: #007bff; }
        .rol-manteniment { background-color: #6f42c1; }
        .rol-caixa { background-color: #20c997; }

        .rol-label {
            min-width: 100px;
            font-weight: bold;
            color: #333;
        }

        .footer {
            text-align: center;
            color: #999;
            font-size: 12px;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
        }

        .user-info {
            background-color: rgba(255, 255, 255, 0.1);
            padding: 10px 15px;
            border-radius: 5px;
            font-size: 14px;
            margin-top: 10px;
        }

        .logout-btn {
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.4);
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
            margin-top: 10px;
            transition: all 0.3s;
        }

        .logout-btn:hover {
            background-color: rgba(255, 255, 255, 0.3);
        }
    </style>
</head>
<body class="dashboard">
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
            <a href="admin_usuarios.php" class="menu-card">
                <div class="menu-icon">👥</div>
                <div class="menu-title">Gestión de Usuários</div>
                <div class="menu-desc">CRUD de usuarios, rols y permisos</div>
            </a>

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

            <a href="historial_general.php" class="menu-card">
                <div class="menu-icon">📊</div>
                <div class="menu-title">Historial General</div>
                <div class="menu-desc">Reporte de ocupaciones totales</div>
            </a>
        </div>

        <!-- ESTADÍSTICAS -->
        <div class="dashboard-grid">
            <!-- Card: Total Usuários -->
            <div class="card">
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

            <!-- Card: Salas -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Salas</div>
                    <div class="card-icon">🏛️</div>
                </div>
                <div class="card-value"><?php echo $total_salas; ?></div>
                <div class="card-label">Salas disponibles</div>
                <a href="admin_salas.php" class="card-link" style="margin-top: 30px;">Gestionar →</a>
            </div>

            <!-- Card: Mesas -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Mesas</div>
                    <div class="card-icon">🪑</div>
                </div>
                <div class="card-value"><?php echo $total_mesas; ?></div>
                <div class="card-label">Mesas registradas</div>
                <a href="admin_mesas.php" class="card-link" style="margin-top: 30px;">Gestionar →</a>
            </div>

            <!-- Card: Ocupaciones Hoy -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Ocupaciones Hoy</div>
                    <div class="card-icon">📅</div>
                </div>
                <div class="card-value"><?php echo $ocupaciones_hoy; ?></div>
                <div class="card-label">Mesas ocupadas hoy</div>
                <a href="historial_general.php" class="card-link" style="margin-top: 20px;">Ver historial →</a>
            </div>
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
        <div class="card" style="background-color: #f0f4ff; border-left: 4px solid #667eea;">
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
