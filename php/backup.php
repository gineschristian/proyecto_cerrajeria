<?php
session_start();
// Solo el admin puede hacer backups
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    die("Acceso denegado");
}

include 'conexion.php';

// Nombre del archivo con la fecha actual
$nombre_archivo = "backup_pinos_" . date("Y-m-d_H-i-s") . ".sql";

// Cabeceras para forzar la descarga del archivo
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename=' . $nombre_archivo);

// Obtener todas las tablas
$tablas = array();
$result = mysqli_query($conexion, "SHOW TABLES");
while ($row = mysqli_fetch_row($result)) {
    $tablas[] = $row[0];
}

$salida = "-- Backup Cerrajería Pinos\n-- Fecha: " . date("Y-m-d H:i:s") . "\n\n";

foreach ($tablas as $tabla) {
    // Estructura de la tabla
    $result = mysqli_query($conexion, "SHOW CREATE TABLE $tabla");
    $row = mysqli_fetch_row($result);
    $salida .= "\n\n" . $row[1] . ";\n\n";

    // Datos de la tabla
    $result = mysqli_query($conexion, "SELECT * FROM $tabla");
    while ($row = mysqli_fetch_assoc($result)) {
        $columnas = array_keys($row);
        $valores = array_map(function($v) use ($conexion) {
            return "'" . mysqli_real_escape_string($conexion, $v) . "'";
        }, array_values($row));
        
        $salida .= "INSERT INTO $tabla (" . implode(", ", $columnas) . ") VALUES (" . implode(", ", $valores) . ");\n";
    }
}

echo $salida;
exit();
?>