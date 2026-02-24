<?php
include 'conexion.php';

$mesActual = date('m');
$anioActual = date('Y');

// Sumar Ingresos del mes
$resIngresos = mysqli_query($conexion, "SELECT SUM(precio_total) as total FROM trabajos WHERE MONTH(fecha) = '$mesActual' AND YEAR(fecha) = '$anioActual'");
$fIngresos = mysqli_fetch_assoc($resIngresos);
$totalIngresos = (float)($fIngresos['total'] ?? 0);

// Sumar Gastos del mes (asumiendo que tu tabla gastos tiene una columna 'fecha')
$resGastos = mysqli_query($conexion, "SELECT SUM(monto) as total FROM gastos WHERE MONTH(fecha) = '$mesActual' AND YEAR(fecha) = '$anioActual'");
$fGastos = mysqli_fetch_assoc($resGastos);
$totalGastos = (float)($fGastos['total'] ?? 0);

header('Content-Type: application/json');
echo json_encode([
    'labels' => ['Ingresos', 'Gastos'],
    'datasets' => [[
        'data' => [$totalIngresos, $totalGastos],
        'backgroundColor' => ['#2ecc71', '#e74c3c'],
        'hoverOffset' => 10
    ]]
]);
?>