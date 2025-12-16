<?php
session_start();
require_once '../database/conexion.php';

// --- Comprobar si hay sesión activa ---
if (!isset($_SESSION['username'])) {
    header("Location: login.php?error=NoSesion");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Selección de Sala - PokéRestaurant</title>
  <link rel="stylesheet" href="./../styles/estilos.css">
</head>
<body>

  <header>
    <span>Pokéfull Stack | <?php echo $_SESSION['username'];?></span>
    <a class="btn-cerrar" href="./historial/historial_general.php">Historial</a>
    <a class="btn-cerrar" href="../processes/logout.php">Cerrar sesión</a>
    <a class="btn-cerrar" href="./admin/admin_usuarios.php">Administrador de Usuarios</a>
    <a class="btn-cerrar" href="./reservas.php">Reservas</a>
  </header>

  <main class="contenedor-selector-sala selector-salas">
    <?php
    try {
        $stmt = $conn->query("SELECT * FROM sala ORDER BY idSala");
        $salas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "<p>Error al cargar las salas.</p>";
        $salas = [];
    }

    foreach ($salas as $sala):
        $id = intval($sala['idSala']);
        $nombre = htmlspecialchars($sala['nombre']);
        
        // Buscar imagen: Prioridad 1: Subida por admin (sala_{id}.*) en img/regiones
        $imgSrc = '';
        $pattern = __DIR__ . "/../img/regiones/sala_{$id}.*";
        $files = glob($pattern);
        
        if (!empty($files)) {
            // Encontramos imagen subida
            $imgSrc = "../img/regiones/" . basename($files[0]);
        } else {
            // Prioridad 2: Imagen legacy (regiones/{Nombre}.png)
            $legacyPath = __DIR__ . "/../img/regiones/" . $sala['nombre'] . ".png";
            if (file_exists($legacyPath)) {
                $imgSrc = "../img/regiones/" . $sala['nombre'] . ".png";
            } else {
                // Fallback
                $imgSrc = "../img/regiones/default.png";
            }
        }
    ?>
    <div class="selector-sala-item">
      <div class="selector-sala-name"><?= $nombre ?></div>
      <a href="./salas/sala.php?idSala=<?= $id ?>" class="selector-sala-button boton-sala" id="boton-sala-<?= $id ?>">
        <?php if ($imgSrc && file_exists(__DIR__ . "/" . $imgSrc)): ?>
            <img src="<?= $imgSrc ?>" alt="<?= $nombre ?>" />
        <?php else: ?>
            <!-- Si no hay imagen física, mostramos un placeholder generado o texto -->
            <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:#ccc;border-radius:10px;">
                <span><?= $nombre ?></span>
            </div>
        <?php endif; ?>
      </a>
    </div>
    <?php endforeach; ?>
  </main>

  <footer>
    <span>Pokéfull Stack &copy; 2024</span>
  </footer>

</body>
</html>
