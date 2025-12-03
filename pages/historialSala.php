<?php
session_start();
require_once '../database/conexion.php'; 

if (!isset($_SESSION['idCamarero'])) {
    header('Location: ../login.php?error=SesionExpirada');
    exit;
}

$idSala = isset($_GET['idSala']) ? intval($_GET['idSala']) : 0;

if ($idSala <= 0) {
    header('Location: ./selecciona_sala.php');
    exit;
}

try {
    $sql = "
        SELECT 
            h.idHistorico,
            m.nombre AS nombreMesa,
            c.nombre AS nombreCamarero,
            c.apellidos AS apellidosCamarero,
            h.horaOcupacion,
            h.horaDesocupacion
        FROM historico h
        INNER JOIN mesa m ON h.idMesa = m.idMesa
        INNER JOIN camarero c ON h.idCamarero = c.idCamarero
        WHERE m.idSala = :idSala
        ORDER BY h.horaOcupacion DESC, h.idHistorico DESC
    ";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':idSala' => $idSala]);
    $historial = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errorMsg = "Error al cargar historial: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Historial de Sala</title>
    <link rel="stylesheet" href="./../../styles/estilos.css">
</head>
<body class="body-sinnoh">
    <header>
        <h1>Historial General de la Sala 
            <?php
            // Obtener el nombre de la sala según el idSala
            $nombreSala = '';
            switch ($idSala) {
                case 1: $nombreSala = 'Kanto'; break;
                case 2: $nombreSala = 'Johto'; break;
                case 3: $nombreSala = 'Hoenn'; break;
                case 4: $nombreSala = 'Sinnoh'; break;
                case 5: $nombreSala = 'Unova'; break;
                case 6: $nombreSala = 'Kalos'; break;
                case 7: $nombreSala = 'Alola'; break;
                case 8: $nombreSala = 'Galar'; break;
                case 9: $nombreSala = 'Paldea'; break;
                default: $nombreSala = 'Desconocida'; break;
            }
            echo htmlspecialchars($nombreSala);
            ?>
        </h1>
        <a class="btn-cerrar" href="./../../processes/logout.php">Cerrar sesión</a>
        
    </header>

    <div class="contenedor-principal">
        <?php if (isset($errorMsg)): ?>
            <p style="color: red;"><?= htmlspecialchars($errorMsg) ?></p>
        <?php elseif (empty($historial)): ?>
                <p>No hay registros en el historial para esta sala.</p>
            <?php else: ?>
                <table class="table-historial">
                    <thead>
                    <tr>
                        <th>ID</th>
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
                            <td><?= htmlspecialchars($h['nombreMesa']) ?></td>
                            <td><?= htmlspecialchars($h['nombreCamarero'] . ' ' . $h['apellidosCamarero']) ?></td>
                            <td><?= htmlspecialchars($h['horaOcupacion']) ?></td>
                            <td><?= ($h['horaDesocupacion'] === '0000-00-00 00:00:00' || !$h['horaDesocupacion']) ? '—' : htmlspecialchars($h['horaDesocupacion']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <div class="volver">
        <?php
        // Volver a la sala unificada
        $urlVolver = './salas/sala.php?idSala=' . $idSala;
        ?>
        <div class="historial-actions"><a href="<?= $urlVolver ?>">← Volver a la sala</a></div>
    </div>

    <footer>
        <span>Pokéfull Stack &copy; 2024</span>
    </footer>
</body>
</html>
