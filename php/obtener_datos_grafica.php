<?php
include 'conexion.php';

$meses = [];
$ingresos = [];
$gastos = [];

// Consultamos los últimos 6 meses
for ($i = 5; $i >= 0; $i--) {
    $fechaMes = date('Y-m', strtotime("-$i months"));
    $nombreMes = date('M', strtotime("-$i months"));
    $meses[] = $nombreMes;

    // Sumar Ingresos (Trabajos + Extras)
    $resI = mysqli_query($conexion, "SELECT SUM(precio_total) as total FROM trabajos WHERE fecha LIKE '$fechaMes%'");
    $resE = mysqli_query($conexion, "SELECT SUM(monto) as total FROM ingresos_b WHERE fecha LIKE '$fechaMes%'");
    $totalI = (mysqli_fetch_assoc($resI)['total'] ?? 0) + (mysqli_fetch_assoc($resE)['total'] ?? 0);
    $ingresos[] = $totalI;

    // Sumar Gastos
    $resG = mysqli_query($conexion, "SELECT SUM(monto) as total FROM gastos WHERE fecha LIKE '$fechaMes%'");
    $gastos[] = mysqli_fetch_assoc($resG)['total'] ?? 0;
}

echo json_encode([
    'labels' => $meses,
    'ingresos' => $ingresos,
    'gastos' => $gastos
]);
?>