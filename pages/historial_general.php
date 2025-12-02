<?php
session_start();
require_once '../database/conexion.php'; // Ajusta la ruta si es distinta

// Verificar sesión
if (!isset($_SESSION['idCamarero'])) {
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
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #333;
            color: #fff;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }
        h1 {
            text-align: center;
            padding: 20px;
            margin: 0;
            background: #222;
            color: #f8c300;
            font-size: 2.2rem;
        }
        table {
            width: 90%;
            margin: 30px auto;
            border-collapse: collapse;
            background-color: #444;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        th, td {
            padding: 12px 15px;
            text-align: center;
        }
        th {
            background-color: #f8c300;
            color: #222;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #555;
        }
        tr:hover {
            background-color: #666;
        }
        .volver {
            text-align: center;
            margin: 30px 0;
        }
        .volver a {
            color: #f8c300;
            text-decoration: none;
            border: 2px solid #f8c300;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: bold;
        }
        .volver a:hover {
            background-color: #f8c300;
            color: #222;
        }
    </style>
</head>
<body>
    <h1>Historial de la mesa: <?= htmlspecialchars($nombreMesa) ?></h1>

     <a href="<?= $urlVolver ?>">← Volver a la sala</a>

    <?php if (empty($historial)): ?>
        <p style="text-align:center; margin-top:30px;">No hay registros de ocupación para esta mesa.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Camarero</th>
                    <th>Fecha de Ocupación</th>
                    <th>Fecha de Desocupación</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($historial as $fila): ?>
                    <tr>
                        <td><?= intval($fila['idHistorico']) ?></td>
                        <td><?= htmlspecialchars($fila['nombreCamarero'] . " " . $fila['apellidosCamarero']) ?></td>
                        <td><?= htmlspecialchars($fila['fechaOcupacion']) ?></td>
                        <td>
                            <?= ($fila['fechaDesocupacion'] === '0000-00-00' || empty($fila['fechaDesocupacion']))
                                ? '<em>En curso</em>'
                                : htmlspecialchars($fila['fechaDesocupacion']) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <div class="volver">
        <?php
        // Detectar sala por parámetro GET
        $idSala = isset($_GET['idSala']) ? intval($_GET['idSala']) : null;
        $urlVolver = './selecciona_sala.php';
        if ($idSala) {
            switch ($idSala) {
                case 1:
                    $urlVolver = './salas/terrazas/kanto.php';
                    break;
                case 2:
                    $urlVolver = './salas/terrazas/johto.php';
                    break;
                case 3:
                    $urlVolver = './salas/terrazas/hoenn.php';
                    break;
                case 4:
                    $urlVolver = './salas/comedores/sinnoh.php';
                    break;
                case 5:
                    $urlVolver = './salas/comedores/unova.php';
                    break;
                case 6:
                    $urlVolver = './salas/salas_privadas/kalos.php';
                    break;
                case 7:
                    $urlVolver = './salas/salas_privadas/alola.php';
                    break;
                case 8:
                    $urlVolver = './salas/salas_privadas/galar.php';
                    break;
                case 9:
                    $urlVolver = './salas/salas_privadas/paldea.php';
                    break;
                default:
                    $urlVolver = './selecciona_sala.php';
                    break;
            }
        }
        ?>
        <a href="<?= $urlVolver ?>">← Volver a la sala</a>
    </div>
</body>
</html>
