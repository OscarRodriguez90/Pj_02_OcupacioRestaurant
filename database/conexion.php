<?php

$srv_name = "localhost";
$db_username = "root";
$db_passwd = "";
$db_name = "bd_pokefullStack";


try {

    $conn = new PDO("mysql:host=$srv_name;dbname=$db_name", $db_username, $db_passwd);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e){

    echo "Error en la conexión con el server de datos: " . $e->getMessage();
    die();

}

?>