<?php

//Mostrar todos lo registros de camareros
session_start();
require_once "../database/conexion.php";
if (!isset($_SESSION['username'])) {
    header("Location: login.php?error=NoSesion");
    exit();
}
try {
    $stmt = $conn->prepare("SELECT * FROM camarero");
    $stmt->execute();
    $camareros = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al obtener camareros: " . $e->getMessage());
}
// Filtrado por nombre y apellido. Se ha de poder ordenar los datos recibidos por apellidos de forma ascendente y descendente

if (isset($_GET['nombre']) || isset($_GET['apellido'])) {
    $nombreFiltro = $_GET['nombre'] ?? '';
    $apellidoFiltro = $_GET['apellido'] ?? '';

    $sql = "SELECT * FROM camarero WHERE 1=1";
    $params = [];

    if (!empty($nombreFiltro)) {
        $sql .= " AND nombre LIKE :nombre";
        $params[':nombre'] = '%' . $nombreFiltro . '%';
    }
    if (!empty($apellidoFiltro)) {
        $sql .= " AND apellidos LIKE :apellidos";
        $params[':apellidos'] = '%' . $apellidoFiltro . '%';
    }

    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $camareros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Error al filtrar camareros: " . $e->getMessage());
    }
    if (isset($_GET['sort']) && ($_GET['sort'] === 'asc' || $_GET['sort'] === 'desc')) {
        $sortOrder = $_GET['sort'];
        usort($camareros, function ($a, $b) use ($sortOrder) {
            if ($sortOrder === 'asc') {
                return strcmp($a['apellidos'], $b['apellidos']);
            } else {
                return strcmp($b['apellidos'], $a['apellidos']);
            }
        });
    }


}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mostrar camareros</title>
</head>
<body>
    <h1>Lista de Camareros</h1>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Apellidos</th>
            <th>Nombre de Usuario</th>
            <th>DNI</th>
            <th>Teléfono</th>
            <th>Email</th>
            <th>Fecha de Contratación</th>
        </tr>
        <?php foreach ($camareros as $camarero): ?>
        <tr>
            <td><?php echo htmlspecialchars($camarero['idCamarero']); ?></td>
            <td><?php echo htmlspecialchars($camarero['nombre']); ?></td>
            <td><?php echo htmlspecialchars($camarero['apellidos']); ?></td>
            <td><?php echo htmlspecialchars($camarero['nombreUsu']); ?></td>
            <td><?php echo htmlspecialchars($camarero['dni']); ?></td>
            <td><?php echo htmlspecialchars($camarero['telefono']); ?></td>
            <td><?php echo htmlspecialchars($camarero['email']); ?></td>
            <td><?php echo htmlspecialchars($camarero['fechaContratacion']); ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

        <!-- Añadir un formulario con 2 inputs para nobre y apellido que filtren de forma sumativa por nombre y apellido de camatero. Esto quiere decir que si se pueda poner cualquier parte del nombre y del segundo cualquier parte del apellido. EL filtro retornara todos aquellos registros que cumplan ambas condiciones -->
    <h2>Filtrar Camareros</h2>
    <form method="GET" action="Camareros.php">
        <label for="nombre">Nombre:</label>
        <input type="text" id="nombre" name="nombre" value="<?php echo isset($_GET['nombre']) ? htmlspecialchars($_GET['nombre']) : ''; ?>">
        <label for="apellido">Apellido:</label>
        <input type="text" id="apellido" name="apellido" value="<?php echo isset($_GET['apellido']) ? htmlspecialchars($_GET['apellido']) : ''; ?>">
        <label for="sort">Ordenar por Apellido:</label>
        <select id="sort" name="sort">
            <option value="">--Selecciona--</option>
            <option value="asc" <?php if (isset($_GET['sort']) && $_GET['sort'] === 'asc') echo 'selected'; ?>>Ascendente</option>
            <option value="desc" <?php if (isset($_GET['sort']) && $_GET['sort'] === 'desc') echo 'selected'; ?>>Descendente</option>
        </select>
        <input type="submit" value="Filtrar">´

    <br><br>
    <a href="insertar_camarero.php">Insertar camarero</a>
    <br><br>
    <a href="selecciona_sala.php">Volver a seleccionar sala</a>

    
</body>
</html>
