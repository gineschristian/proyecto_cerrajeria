<?php
// php/consultar_impuestos.php
include 'conexion.php';

// 1. Filtros de fecha (Mes actual por defecto)
$inicio = $_GET['inicio'] ?? date('Y-m-01');
$fin = $_GET['fin'] ?? date('Y-m-t');

// 2. FACTURACIÓN EMITIDA (Ingresos con Factura - Tipo A)
$sql_emitida = "SELECT SUM(precio_total) as total FROM trabajos 
                WHERE factura = 1 AND fecha BETWEEN '$inicio' AND '$fin'";
$res_emitida = mysqli_query($conexion, $sql_emitida);
$total_emitida_iva_inc = mysqli_fetch_assoc($res_emitida)['total'] ?? 0;
$iva_emitido = $total_emitida_iva_inc - ($total_emitida_iva_inc / 1.21);

// 3. FACTURACIÓN SOPORTADA (Gastos con IVA)
$sql_soportada = "SELECT SUM(monto) as total FROM gastos 
                  WHERE fecha BETWEEN '$inicio' AND '$fin'";
$res_soportada = mysqli_query($conexion, $sql_soportada);
$total_soportada_iva_inc = mysqli_fetch_assoc($res_soportada)['total'] ?? 0;
$iva_soportado = $total_soportada_iva_inc - ($total_soportada_iva_inc / 1.21);

// 4. MODELO 303 (Diferencia de IVA)
$iva_a_ingresar = $iva_emitido - $iva_soportado;

// 5. BENEFICIO BRUTO (A + B - Gastos)
$sql_extras = "SELECT SUM(precio_total) as total FROM trabajos 
               WHERE factura = 0 AND fecha BETWEEN '$inicio' AND '$fin'";
$res_extras = mysqli_query($conexion, $sql_extras);
$total_extras_b = mysqli_fetch_assoc($res_extras)['total'] ?? 0;

$beneficio_bruto = ($total_emitida_iva_inc + $total_extras_b) - $total_soportada_iva_inc;
?>