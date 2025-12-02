<?php
session_start();

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
  <link rel="stylesheet" href="./../styles/styles.css">
</head>
<body>

  <header>
    <span>Pokéfull Stack | <?php echo $_SESSION['username'];?></span>
    <a class="btn-cerrar" href="./historial_general.php">Historial</a>
    <a class="btn-cerrar" href="../processes/logout.php">Cerrar sesión</a>
    <a class="btn-cerrar" href="./camareros.php">Camareros</a>
  </header>

  <main class="contenedor-selector-sala">
    <!-- Comedor -->
    <a href="./salas/sala.php?idSala=4" class="boton-sala" id="boton-sala1">
      <!-- <img src="../img/Logo_Comedor.png" alt="Comedor"> -->
    </a>

    <!-- Terraza -->
    <a href="./salas/sala.php?idSala=1" class="boton-sala" id="boton-sala2">
      <!-- <img src="../img/Logo_Terraza.png" alt="Terraza"> -->
    </a>

    <!-- Sala privada -->
    <a href="./salas/sala.php?idSala=7" class="boton-sala" id="boton-sala3">
      <!-- <img src="../img/Logo_SalaPrivada.png" alt="Sala privada"> -->
    </a>
  </main>

  <footer>
    <span>Pokéfull Stack &copy; 2024</span>
  </footer>

</body>
</html>
