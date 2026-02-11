<?php
// php/obtener_datos_grafica.php
header('Content-Type: application/json');
include 'conexion.php';

$labels = [];
$ingresos = [];
$gastos = [];

// Calculamos los últimos 6 meses
for ($i = 5; $i >= 0; $i--) {
    $fecha_mes = date('Y-m', strtotime("-$i month"));
    $nombre_mes = date('M', strtotime("-$i month")); // Ene, Feb, Mar...
    
    $labels[] = $nombre_mes;

    // 1. Sumar Ingresos del mes (Trabajos A + Trabajos B)
    $sql_i = "SELECT SUM(precio_total) as total FROM trabajos 
              WHERE fecha LIKE '$fecha_mes%'";
    $res_i = mysqli_query($conexion, $sql_i);
    $row_i = mysqli_fetch_assoc($res_i);
    $total_i = $row_i['total'] ?? 0;

    // Sumamos también ingresos_b si tienes esa tabla separada
    $sql_eb = "SELECT SUM(monto) as total FROM ingresos_b WHERE fecha LIKE '$fecha_mes%'";
    $res_eb = mysqli_query($conexion, $sql_eb);
    $total_eb = mysqli_fetch_assoc($res_eb)['total'] ?? 0;
    
    $ingresos[] = (float)($total_i + $total_eb);

    // 2. Sumar Gastos del mes
    $sql_g = "SELECT SUM(monto) as total FROM gastos 
              WHERE fecha LIKE '$fecha_mes%'";
    $res_g = mysqli_query($conexion, $sql_g);
    $row_g = mysqli_fetch_assoc($res_g);
    $gastos[] = (float)($row_g['total'] ?? 0);
}

// Enviamos los datos en formato JSON para Chart.js
echo json_encode([
    'labels' => $labels,
    'ingresos' => $ingresos,
    'gastos' => $gastos
]);