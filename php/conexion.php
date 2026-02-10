<?php
// Establecemos los datos de configuracion de tu servidor local (XAMPP)
$host ="localhost";
$user ="root";
$pass = "";
$db = "cerrajeria_pinos";

$conexion = new mysqli($host,$user,$pass, $db);

if ($conexion->connect_error) {
    die("Error de conexion:" . $conexion->connect_error);
}

$conexion->set_charset ("utf8");
?>