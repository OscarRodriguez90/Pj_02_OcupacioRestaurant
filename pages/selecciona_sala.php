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
  <link rel="stylesheet" href="./../styles/estilos.css">
</head>
<body>

  <header>
    <span>Pokéfull Stack | <?php echo $_SESSION['username'];?></span>
    <a class="btn-cerrar" href="./historial_general.php">Historial</a>
    <a class="btn-cerrar" href="../processes/logout.php">Cerrar sesión</a>
    <a class="btn-cerrar" href="./camareros.php">Camareros</a>
  </header>

  <main class="contenedor-selector-sala selector-salas">
    <!-- Sala: Kanto (id=1) -->
    <div class="selector-sala-item">
      <div class="selector-sala-name">Kanto</div>
      <a href="./salas/sala.php?idSala=1" class="selector-sala-button boton-sala" id="boton-sala-kanto">
        <img src="../img/regiones/Kanto.png" alt="Kanto" />
      </a>
    </div>

    <!-- Sala: Johto (id=2) -->
    <div class="selector-sala-item">
      <div class="selector-sala-name">Johto</div>
      <a href="./salas/sala.php?idSala=2" class="selector-sala-button boton-sala" id="boton-sala-johto">
        <img src="../img/regiones/Johto.png" alt="Johto" />
      </a>
    </div>

    <!-- Sala: Hoenn (id=3) -->
    <div class="selector-sala-item">
      <div class="selector-sala-name">Hoenn</div>
      <a href="./salas/sala.php?idSala=3" class="selector-sala-button boton-sala" id="boton-sala-hoenn">
        <img src="../img/regiones/Hoenn.png" alt="Hoenn" />
      </a>
    </div>

    <!-- Sala: Sinnoh (id=4) -->
    <div class="selector-sala-item">
      <div class="selector-sala-name">Sinnoh</div>
      <a href="./salas/sala.php?idSala=4" class="selector-sala-button boton-sala" id="boton-sala-sinnoh">
        <img src="../img/regiones/Sinnoh.png" alt="Sinnoh" />
      </a>
    </div>

    <!-- Sala: Unova (id=5) -->
    <div class="selector-sala-item">
      <div class="selector-sala-name">Unova</div>
      <a href="./salas/sala.php?idSala=5" class="selector-sala-button boton-sala" id="boton-sala-unova">
        <img src="../img/regiones/Unova.png" alt="Unova" />
      </a>
    </div>

    <!-- Sala: Kalos (id=6) -->
    <div class="selector-sala-item">
      <div class="selector-sala-name">Kalos</div>
      <a href="./salas/sala.php?idSala=6" class="selector-sala-button boton-sala" id="boton-sala-kalos">
        <img src="../img/regiones/Kalos.png" alt="Kalos" />
      </a>
    </div>

    <!-- Sala: Alola (id=7) -->
    <div class="selector-sala-item">
      <div class="selector-sala-name">Alola</div>
      <a href="./salas/sala.php?idSala=7" class="selector-sala-button boton-sala" id="boton-sala-alola">
        <img src="../img/regiones/Alola.png" alt="Alola" />
      </a>
    </div>

    <!-- Sala: Galar (id=8) -->
    <div class="selector-sala-item">
      <div class="selector-sala-name">Galar</div>
      <a href="./salas/sala.php?idSala=8" class="selector-sala-button boton-sala" id="boton-sala-galar">
        <img src="../img/regiones/Galar.png" alt="Galar" />
      </a>
    </div>

    <!-- Sala: Paldea (id=9) -->
    <div class="selector-sala-item">
      <div class="selector-sala-name">Paldea</div>
      <a href="./salas/sala.php?idSala=9" class="selector-sala-button boton-sala" id="boton-sala-paldea">
        <img src="../img/regiones/Paldea.png" alt="Paldea" />
      </a>
    </div>
  </main>

  <footer>
    <span>Pokéfull Stack &copy; 2024</span>
  </footer>

</body>
</html>
