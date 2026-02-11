<?php
include 'conexion.php';

$inicio = $_GET['inicio'] ?? date('Y-m-01');
$fin = $_GET['fin'] ?? date('Y-m-t');
$gastoSimulado = isset($_GET['simulacion']) ? floatval($_GET['simulacion']) : 0;

// --- CONFIGURACIÓN GASTOS FIJOS AUTÓNOMO ---
$cuotaAutonomo = 300.00; 
$gestoria = 60.00;       
$gastosFijosMes = $cuotaAutonomo + $gestoria;

// 1. FACTURA EMITIDA (Ventas con Factura)
$sqlI = "SELECT SUM(precio_total) as total FROM trabajos 
         WHERE factura = 1 AND fecha BETWEEN '$inicio 00:00:00' AND '$fin 23:59:59'";
$resI = mysqli_query($conexion, $sqlI);
$totalEmitidaIVA = mysqli_fetch_assoc($resI)['total'] ?? 0;
$baseVentasA_Facturadas = $totalEmitidaIVA / 1.21;
$ivaEmitido = $totalEmitidaIVA - $baseVentasA_Facturadas;

// 2. FACTURA SOPORTADA (Gastos con factura)
$sqlG = "SELECT SUM(monto) as total FROM gastos 
         WHERE con_factura = 1 
         AND fecha BETWEEN '$inicio 00:00:00' AND '$fin 23:59:59'";
$resG = mysqli_query($conexion, $sqlG);
$totalGastoFacturadoReal = mysqli_fetch_assoc($resG)['total'] ?? 0;

$totalSoportadaIVA = $totalGastoFacturadoReal + $gastoSimulado;
$baseGastosConFactura = $totalSoportadaIVA / 1.21;
$ivaSoportado = $totalSoportadaIVA - $baseGastosConFactura;

// 3. RESULTADO MODELO 303 (IVA)
$ivaResultado303 = $ivaEmitido - $ivaSoportado;
$inversionIVARecomendada = ($ivaResultado303 > 0) ? ($ivaResultado303 / 0.21) : 0;

// 4. CÁLCULO DE BASES PARA BENEFICIOS
$sqlAT = "SELECT SUM(precio_total) as total FROM trabajos WHERE fecha BETWEEN '$inicio 00:00:00' AND '$fin 23:59:59'";
$totalTrabajosA_Bruto = mysqli_fetch_assoc(mysqli_query($conexion, $sqlAT))['total'] ?? 0;
$baseVentasTotales = $totalTrabajosA_Bruto / 1.21;

$sqlB = "SELECT SUM(monto) as total FROM ingresos_b WHERE fecha BETWEEN '$inicio' AND '$fin'";
$totalExtrasB = mysqli_fetch_assoc(mysqli_query($conexion, $sqlB))['total'] ?? 0;

$sqlGTN = "SELECT SUM(monto) as total FROM gastos WHERE con_factura = 0 AND fecha BETWEEN '$inicio 00:00:00' AND '$fin 23:59:59'";
$totalGastosSinFactura = mysqli_fetch_assoc(mysqli_query($conexion, $sqlGTN))['total'] ?? 0;

// --- CÁLCULOS FINALES ---
$beneficioBrutoActividad = ($baseVentasTotales + $totalExtrasB) - ($baseGastosConFactura + $totalGastosSinFactura);
$previsionIRPF = ($beneficioBrutoActividad > 0) ? ($beneficioBrutoActividad * 0.15) : 0;

// BENEFICIO NETO REAL (Descontando IRPF y Gastos Fijos)
$beneficioNetoReal = $beneficioBrutoActividad - $previsionIRPF - $gastosFijosMes;
?>

<?php if($gastoSimulado > 0): ?>
    <div style="grid-column: 1 / -1; background: #fcf3cf; padding: 15px; border-radius: 8px; text-align: center; border: 1px dashed #f39c12; margin-bottom: 15px;">
        ⚠️ <strong>MODO SIMULACIÓN ACTIVO:</strong> Viendo impacto de compra extra de <strong><?php echo number_format($gastoSimulado, 2); ?>€</strong>.
    </div>
<?php endif; ?>

<div class="card-impuesto iva-soportado">
    <h3>Factura Soportada</h3>
    <h2><?php echo number_format($totalSoportadaIVA, 2); ?>€</h2>
    <small>IVA deducido: <?php echo number_format($ivaSoportado, 2); ?>€</small>
</div>

<div class="card-impuesto" style="background: linear-gradient(135deg, #34495e, #2c3e50);">
    <h3>Factura Emitida</h3>
    <h2><?php echo number_format($totalEmitidaIVA, 2); ?>€</h2>
    <small>IVA recaudado: <?php echo number_format($ivaEmitido, 2); ?>€</small>
</div>

<div class="card-impuesto" style="background: <?php echo ($ivaResultado303 > 0) ? 'linear-gradient(135deg, #e74c3c, #c0392b)' : 'linear-gradient(135deg, #27ae60, #1e8449)'; ?>;">
    <h3>Modelo 303 (IVA)</h3>
    <h2><?php echo number_format(abs($ivaResultado303), 2); ?>€</h2>
    
    <?php if ($ivaResultado303 > 0): ?>
        <div style="background: rgba(255,255,255,0.2); margin-top:10px; padding: 8px; border-radius: 5px; font-size: 0.8rem;">
            <strong>📉 RESULTADO: A INGRESAR</strong><br>
            Invertir <b><?php echo number_format($inversionIVARecomendada, 2); ?>€</b> anula este pago.
        </div>
    <?php else: ?>
        <div style="background: rgba(255,255,255,0.2); margin-top:10px; padding: 8px; border-radius: 5px; font-size: 0.8rem;">
            <strong>📈 RESULTADO: A DEVOLVER</strong><br>
            Hacienda te compensará este saldo.
        </div>
    <?php endif; ?>
</div>

<div class="card-impuesto" style="background: linear-gradient(135deg, #f39c12, #d35400);">
    <h3>Previsión IRPF (15%)</h3>
    <h2><?php echo number_format($previsionIRPF, 2); ?>€</h2>
    <small>Sobre beneficio imponible</small>
</div>

<div class="card-impuesto beneficio-neto">
    <h3>Beneficio Bruto</h3>
    <h2><?php echo number_format($beneficioBrutoActividad, 2); ?>€</h2>
    <small>Ingresos - Gastos variables</small>
</div>

<div class="card-impuesto" style="background: linear-gradient(135deg, #8e44ad, #2c3e50);">
    <h3>BENEFICIO NETO REAL</h3>
    <h2 style="font-size: 2.2em;"><?php echo number_format($beneficioNetoReal, 2); ?>€</h2>
    
    <div style="background: rgba(0,0,0,0.3); margin-top:10px; padding: 12px; border-radius: 8px; font-size: 0.85rem; text-align: left;">
        <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
            <span>Beneficio Actividad:</span>
            <span><?php echo number_format($beneficioBrutoActividad, 2); ?>€</span>
        </div>
        <div style="display: flex; justify-content: space-between; color: #ff9f9f; margin-bottom: 4px;">
            <span>- IRPF (15%):</span>
            <span><?php echo number_format($previsionIRPF, 2); ?>€</span>
        </div>
        <div style="display: flex; justify-content: space-between; color: #ff9f9f; margin-bottom: 4px;">
            <span>- Gastos Fijos (SS+Gest):</span>
            <span><?php echo number_format($gastosFijosMes, 2); ?>€</span>
        </div>
        <hr style="border: 0; border-top: 1px solid rgba(255,255,255,0.2); margin: 8px 0;">
        <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 1rem; color: #2ecc71;">
            <span>LIMPIO FINAL:</span>
            <span><?php echo number_format($beneficioNetoReal, 2); ?>€</span>
        </div>
    </div>
</div>