<?php
include 'conexion.php';

// Filtramos por el mes y año actual
$mesActual = date('m');
$anioActual = date('Y');

$sql = "SELECT localidad, SUM(precio_total) as total 
        FROM trabajos 
        WHERE localidad IS NOT NULL AND localidad != ''
        AND MONTH(fecha) = '$mesActual' 
        AND YEAR(fecha) = '$anioActual'
        GROUP BY localidad 
        ORDER BY total DESC 
        LIMIT 5";

$res = mysqli_query($conexion, $sql);

$labels = [];
$totales = [];

while($fila = mysqli_fetch_assoc($res)) {
    $labels[] = strtoupper($fila['localidad']);
    $totales[] = (float)$fila['total'];
}

header('Content-Type: application/json');
echo json_encode([
    'labels' => $labels,
    'datasets' => [[
        'label' => 'Facturado este mes (€)',
        'data' => $totales,
        'backgroundColor' => ['#3498db', '#5dade2', '#85c1e9', '#aed6f1', '#d6eaf8'],
        'borderRadius' => 5
    ]]
]);
?>