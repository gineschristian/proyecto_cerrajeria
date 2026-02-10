<?php
include 'conexion.php';

$inicio = $_GET['inicio'] ?? date('Y-m-01');
$fin = $_GET['fin'] ?? date('Y-m-t');

// 1. IVA Repercutido (Ventas con factura)
$sqlI = "SELECT SUM(precio_total) as total FROM trabajos 
         WHERE factura = 1 AND fecha BETWEEN '$inicio 00:00:00' AND '$fin 23:59:59'";
$resI = mysqli_query($conexion, $sqlI);
$totalConFactura = mysqli_fetch_assoc($resI)['total'] ?? 0;
$ivaRepercutido = $totalConFactura - ($totalConFactura / 1.21);

// 2. IVA Soportado (Compras con factura)
// Ahora que añadimos la columna, esta consulta ya no dará error
$sqlG = "SELECT SUM(monto) as total FROM gastos 
         WHERE factura = 1 AND fecha BETWEEN '$inicio 00:00:00' AND '$fin 23:59:59'";
$resG = mysqli_query($conexion, $sqlG);
$gastosConFactura = mysqli_fetch_assoc($resG)['total'] ?? 0;
$ivaSoportado = $gastosConFactura - ($gastosConFactura / 1.21);

// 3. Totales Generales para Beneficio Neto
$sqlG_Total = "SELECT SUM(monto) as total FROM gastos WHERE fecha BETWEEN '$inicio' AND '$fin'";
$totalGastosReal = mysqli_fetch_assoc(mysqli_query($conexion, $sqlG_Total))['total'] ?? 0;

$sqlE = "SELECT SUM(monto) as total FROM ingresos_b WHERE fecha BETWEEN '$inicio' AND '$fin'";
$totalExtras = mysqli_fetch_assoc(mysqli_query($conexion, $sqlE))['total'] ?? 0;

$sqlT_Total = "SELECT SUM(precio_total) as total FROM trabajos WHERE fecha BETWEEN '$inicio' AND '$fin'";
$totalTrabajosReal = mysqli_fetch_assoc(mysqli_query($conexion, $sqlT_Total))['total'] ?? 0;

$ivaAPagar = $ivaRepercutido - $ivaSoportado;
$beneficioNeto = ($totalTrabajosReal + $totalExtras) - $totalGastosReal - ($ivaAPagar > 0 ? $ivaAPagar : 0);
?>

<div class="card-impuesto iva-pagar">
    <h3>IVA a Pagar (Modelo 303)</h3>
    <h2><?php echo number_format(max(0, $ivaAPagar), 2); ?>€</h2>
    <small>Basado en facturas emitidas</small>
</div>

<div class="card-impuesto iva-soportado">
    <h3>IVA Soportado (Deducible)</h3>
    <h2><?php echo number_format($ivaSoportado, 2); ?>€</h2>
    <small>De tus gastos con factura</small>
</div>

<div class="card-impuesto beneficio-neto">
    <h3>Beneficio Neto Real</h3>
    <h2><?php echo number_format($beneficioNeto, 2); ?>€</h2>
    <small>Ingresos totales - Gastos - IVA</small>
</div>