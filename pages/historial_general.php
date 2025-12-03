<?php
session_start();
require_once '../database/conexion.php'; // Ajusta la ruta si es distinta

// Verificar sesión
if (!isset($_SESSION['idCamarero'])) {
    header('Location: ./login.php?error=SesionExpirada');
    exit;
}

// Ahora: Historial general (todas las salas)
try {
    $sql = "
        SELECT 
            h.idHistorico,
            s.nombre AS salaNombre,
            m.nombre AS mesaNombre,
            c.nombre AS nombreCamarero,
            c.apellidos AS apellidosCamarero,
            h.horaOcupacion,
            h.horaDesocupacion
        FROM historico h
        INNER JOIN mesa m ON h.idMesa = m.idMesa
        INNER JOIN sala s ON m.idSala = s.idSala
        INNER JOIN camarero c ON h.idCamarero = c.idCamarero
        ORDER BY h.horaOcupacion DESC, h.idHistorico DESC
    ";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $historial = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errorMsg = "Error al cargar historial general: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Historial General</title>
    <link rel="stylesheet" href="../styles/estilos.css">
</head>
<body class="body-sinnoh">
    <h1 class="historial-header">Historial General de Mesas</h1>

    <div class="contenedor-principal">
        <?php if (isset($errorMsg)): ?>
            <p style="color: red;"><?= htmlspecialchars($errorMsg) ?></p>
        <?php elseif (empty($historial)): ?>
            <p>No hay registros en el historial.</p>
        <?php else: ?>
            <table class="table-historial">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Sala</th>
                        <th>Mesa</th>
                        <th>Camarero</th>
                        <th>Hora Ocupación</th>
                        <th>Hora Desocupación</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($historial as $h): ?>
                        <tr>
                            <td><?= intval($h['idHistorico']) ?></td>
                            <td><?= htmlspecialchars($h['salaNombre']) ?></td>
                            <td><?= htmlspecialchars($h['mesaNombre']) ?></td>
                            <td><?= htmlspecialchars($h['nombreCamarero'] . ' ' . $h['apellidosCamarero']) ?></td>
                            <td><?= htmlspecialchars($h['horaOcupacion']) ?></td>
                            <td><?= ($h['horaDesocupacion'] === '0000-00-00 00:00:00' || empty($h['horaDesocupacion'])) ? '—' : htmlspecialchars($h['horaDesocupacion']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <div class="historial-actions">
        <a href="./selecciona_sala.php">← Volver a selección de salas</a>
    </div>

    <footer>
        <span>Pokéfull Stack &copy; 2025</span>
    </footer>
</body>
</html>
