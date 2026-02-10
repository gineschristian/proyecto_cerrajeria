<?php
include 'conexion.php';

// Indicamos al navegador que la respuesta es JSON
header('Content-Type: application/json');

// Consultamos productos con stock bajo (menos de 5 unidades)
$sql = "SELECT nombre, cantidad FROM productos WHERE cantidad < 5 ORDER BY cantidad ASC";
$res = mysqli_query($conexion, $sql);

$alertas = [];

if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        // Forzamos que la cantidad sea tratada como número
        $row['cantidad'] = (int)$row['cantidad'];
        $alertas[] = $row;
    }
}

// Enviamos los datos
echo json_encode($alertas);
?>