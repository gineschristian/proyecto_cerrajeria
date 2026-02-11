<?php
// Detectar si estamos en localhost o en el servidor real
$whitelist = array('127.0.0.1', '::1');
$is_localhost = in_array($_SERVER['REMOTE_ADDR'], $whitelist) || $_SERVER['SERVER_NAME'] == 'localhost';

if ($is_localhost) {
    // CONFIGURACIÓN PARA TU PC (XAMPP / WAMP)
    $host = "localhost";
    $user = "root";
    $pass = ""; // Generalmente vacío en XAMPP
    $db   = "cerrajeria_pinos"; // Nombre de tu base de datos local
} else {
    // CONFIGURACIÓN PARA INFINITYFREE
    $host = "sql307.epizy.com"; 
    $user = "if0_41123910";
    $pass = "g5EBDsqekmIOi"; // Tu contraseña de vPanel verificada
    $db   = "if0_41123910_cerrajeria";
}

$conexion = mysqli_connect($host, $user, $pass, $db);

if (!$conexion) {
    die("Error de conexión (" . ($is_localhost ? "Local" : "Remoto") . "): " . mysqli_connect_error());
}

mysqli_set_charset($conexion, "utf8");
?>