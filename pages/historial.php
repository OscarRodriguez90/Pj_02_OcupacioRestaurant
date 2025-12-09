<?php
session_start();
require_once '../database/conexion.php'; // Ajusta la ruta si es distinta

// Verificar sesión: permitir acceso a cualquier usuario logueado
if (!isset($_SESSION['rol'])) {
    header('Location: ./login.php?error=SesionExpirada');
    exit;
}

// Verificar idMesa
if (!isset($_GET['idMesa']) || !is_numeric($_GET['idMesa'])) {
    die("ID de mesa no válido.");
}

$idMesa = intval($_GET['idMesa']);
$nombreMesa = "";

// --- 1. Obtener el nombre de la mesa ---
try {
    $stmt = $conn->prepare("SELECT nombre FROM mesa WHERE idMesa = :id");
    $stmt->execute([':id' => $idMesa]);
    $mesa = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($mesa) {
        $nombreMesa = $mesa['nombre'];
    } else {
        die("Mesa no encontrada.");
    }
} catch (PDOException $e) {
    die("Error al obtener mesa: " . $e->getMessage());
}

// --- 2. Obtener el historial ---
try {
    $sql = "
        SELECT 
            h.idHistorico,
            c.nombre AS nombreCamarero,
            c.apellidos AS apellidosCamarero,
            h.horaOcupacion,
            h.horaDesocupacion
        FROM historico h
        INNER JOIN camarero c ON h.idCamarero = c.idCamarero
        WHERE h.idMesa = :idMesa
        ORDER BY h.idHistorico DESC
    ";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':idMesa' => $idMesa]);
    $historial = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al cargar historial: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Historial de <?= htmlspecialchars($nombreMesa) ?></title>
    <link rel="stylesheet" href="../styles/estilos.css">
    <meta name="viewport" content="width=device-width,initial-scale=1">
</head>
<body class="body-sinnoh">
    <h1 class="historial-header">Historial de la mesa: <?= htmlspecialchars($nombreMesa) ?></h1>

    <?php if (empty($historial)): ?>
        <p style="text-align:center; margin-top:30px;">No hay registros de ocupación para esta mesa.</p>
    <?php else: ?>
        <table class="table-historial">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Camarero</th>
                    <th>Hora de Ocupación</th>
                    <th>Hora de Desocupación</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($historial as $fila): ?>
                    <tr>
                        <td><?= intval($fila['idHistorico']) ?></td>
                        <td><?= htmlspecialchars($fila['nombreCamarero'] . " " . $fila['apellidosCamarero']) ?></td>
                        <td><?= htmlspecialchars($fila['horaOcupacion']) ?></td>
                        <td>
                            <?= ($fila['horaDesocupacion'] === '0000-00-00 00:00:00' || empty($fila['horaDesocupacion']))
                                ? '<em>En curso</em>'
                                : htmlspecialchars($fila['horaDesocupacion']) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <div class="volver">
        <?php
        // Detectar sala por parámetro GET y volver a la sala unificada
        $idSala = isset($_GET['idSala']) ? intval($_GET['idSala']) : null;
        $urlVolver = './selecciona_sala.php';
        if ($idSala) {
            $urlVolver = './salas/sala.php?idSala=' . $idSala;
        }
        ?>
        <a href="<?= $urlVolver ?>">← Volver a la sala</a>
    </div>
</body>
</html>
