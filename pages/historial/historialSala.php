<?php
session_start();
require_once '../../database/conexion.php'; 

// Verificar sesión: permitir legacy `idCamarero` o usuarios con rol `camarero`/`admin`
$rol = $_SESSION['rol'] ?? null;
if (!(isset($_SESSION['idCamarero']) || $rol === 'camarero' || $rol === 'admin')) {
    header('Location: ../login.php?error=SesionExpirada');
    exit;
}

$idSala = isset($_GET['idSala']) ? intval($_GET['idSala']) : 0;

if ($idSala <= 0) {
    header('Location: ../selecciona_sala.php');
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

// Determinar nombre de sala y clase de body según el id
$nombreSala = '';
$bodyClass = '';
switch ($idSala) {
    case 1: $nombreSala = 'Kanto'; $bodyClass = 'body-kanto'; break;
    case 2: $nombreSala = 'Johto'; $bodyClass = 'body-johto'; break;
    case 3: $nombreSala = 'Hoenn'; $bodyClass = 'body-hoenn'; break;
    case 4: $nombreSala = 'Sinnoh'; $bodyClass = 'body-sinnoh'; break;
    case 5: $nombreSala = 'Unova'; $bodyClass = 'body-unova'; break;
    case 6: $nombreSala = 'Kalos'; $bodyClass = 'body-kalos'; break;
    case 7: $nombreSala = 'Alola'; $bodyClass = 'body-alola'; break;
    case 8: $nombreSala = 'Galar'; $bodyClass = 'body-galar'; break;
    case 9: $nombreSala = 'Paldea'; $bodyClass = 'body-paldea'; break;
    default: $nombreSala = 'Desconocida'; $bodyClass = ''; break;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Historial de Sala</title>
    <link rel="stylesheet" href="../../styles/estilos.css">
</head>
<body class="<?php echo $bodyClass ?: 'page-sala'; ?>">
    <header>
        <h1>Historial General de la Sala 
            <?php echo htmlspecialchars($nombreSala); ?>
        </h1>
        <a class="btn-cerrar" href="../../processes/logout.php">Cerrar sesión</a>
        
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
        $urlVolver = '../salas/sala.php?idSala=' . $idSala;
        ?>
        <div class="historial-actions"><a href="<?= $urlVolver ?>">← Volver a la sala</a></div>
    </div>

    <footer>
        <span>Pokéfull Stack &copy; 2024</span>
    </footer>
</body>
</html>
