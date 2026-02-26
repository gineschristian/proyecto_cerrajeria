<?php
include 'conexion.php';
session_start();

// Seguridad: Solo Admin
if (!isset($_SESSION['rol']) || strtolower(trim($_SESSION['rol'])) !== 'admin') {
    exit("Acceso denegado");
}

$proveedor_nom = $_GET['nombre'] ?? '';
$inicio = $_GET['inicio'] ?? ''; // Captura fecha inicio
$fin = $_GET['fin'] ?? '';       // Captura fecha fin

// Si intentan entrar sin un nombre de proveedor, los devolvemos
if (empty($proveedor_nom)) {
    header("Location: ../admin/proveedores.php");
    exit;
}

// Consultar los gastos de este proveedor
$p_limpio = mysqli_real_escape_string($conexion, $proveedor_nom);

// Construcción de la consulta con filtro de fecha
$query = "SELECT * FROM gastos WHERE proveedor = '$p_limpio'";

if (!empty($inicio) && !empty($fin)) {
    $inicioClean = mysqli_real_escape_string($conexion, $inicio);
    $finClean = mysqli_real_escape_string($conexion, $fin);
    $query .= " AND fecha BETWEEN '$inicioClean' AND '$finClean'";
}

$query .= " ORDER BY fecha DESC";
$res = mysqli_query($conexion, $query);

$total = 0;
$filas_html = "";

if ($res && mysqli_num_rows($res) > 0) {
    while($row = mysqli_fetch_assoc($res)){
        $total += $row['monto'];
        $filas_html .= "
            <tr>
                <td>".date('d/m/Y', strtotime($row['fecha']))."</td>
                <td>".htmlspecialchars($row['concepto'])."</td>
                <td style='text-align:right; font-weight:bold;'>".number_format($row['monto'], 2, ',', '.')."€</td>
            </tr>";
    }
} else {
    $filas_html = "<tr><td colspan='3' style='text-align:center; padding:20px;'>No hay gastos registrados en este periodo.</td></tr>";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial: <?php echo htmlspecialchars($proveedor_nom); ?></title>
    <link rel="stylesheet" href="../css/main.css">
    <style>
        body { background-color: #f0f2f5; font-family: sans-serif; }
        header { 
            background: #2c3e50; 
            color: white; 
            padding: 15px 20px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
        }
        .container { padding: 30px; max-width: 1000px; margin: auto; }
        
        /* Estilos para la cabecera de impresión (Nueva sección) */
        .print-header {
            display: none; /* Se oculta en web, se muestra en print */
            align-items: center;
            gap: 20px;
            margin-bottom: 20px;
        }
        .print-header .item { display: flex; align-items: center; gap: 8px; }
        .print-header .label { font-weight: bold; color: #7f8c8d; font-size: 1.1rem; }
        .print-header .date { color: #bdc3c7; font-size: 0.9rem; }

        /* Tarjeta del Total */
        .card-total {
            background: white;
            border-radius: 12px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 25px;
        }
        .card-total h2 { margin: 0; color: #333; font-size: 1.5rem; text-transform: uppercase; }
        .card-total p { margin: 5px 0; color: #888; font-weight: bold; font-size: 0.9rem; }
        .monto-grande { color: #e74c3c; font-size: 3rem; font-weight: 800; margin-top: 10px; }

        /* Tarjeta de la Tabla */
        .card-tabla {
            background: white;
            border-radius: 12px;
            padding: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        table { width: 100%; border-collapse: collapse; }
        th { 
            text-align: left; 
            padding: 15px; 
            border-bottom: 2px solid #eee; 
            color: #333;
            background: #fafafa;
        }
        td { padding: 15px; border-bottom: 1px solid #eee; color: #555; }
        tr:last-child td { border-bottom: none; }
        
        .btn-volver {
            background: #3498db;
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 5px;
            font-size: 0.9rem;
        }

        .btn-pdf {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
            margin-left: 10px;
        }

        .info-filtro {
            font-size: 0.85rem;
            color: #bdc3c7;
            margin-top: 5px;
        }

        @media print {
            header, .btn-pdf, .btn-volver {
                display: none !important;
            }
            .print-header {
                display: flex !important; /* Mostramos la cabecera al imprimir */
            }
            body { background: white; }
            .container { padding: 0; max-width: 100%; }
            .card-total, .card-tabla { box-shadow: none; border: 1px solid #eee; }
        }
    </style>
</head>
<body>

<header>
    <div style="display:flex; flex-direction:column;">
        <div style="display:flex; align-items:center; gap:10px;">
            <span style="font-size:1.2rem;">🚚</span>
            <h1 style="margin:0; font-size:1.2rem;">Historial Proveedor: <?php echo htmlspecialchars($proveedor_nom); ?></h1>
        </div>
        <?php if(!empty($inicio)): ?>
            <div class="info-filtro">Periodo: <?php echo date('d/m/Y', strtotime($inicio)); ?> - <?php echo date('d/m/Y', strtotime($fin)); ?></div>
        <?php endif; ?>
    </div>
    <div>
        <button onclick="window.print()" class="btn-pdf">📄 Generar PDF / Imprimir</button>
        <a href="../admin/proveedores.php" class="btn-volver">⬅️ Volver</a>
    </div>
</header>

<div class="container">

    <div class="print-header">
        <div class="item">
            <span style="font-size:1.5rem; color:#f39c12;">📁</span>
            <span class="label">Proveedor: <?php echo strtoupper(htmlspecialchars($proveedor_nom)); ?></span>
        </div>
        <?php if(!empty($inicio)): ?>
        <div class="item">
            <span style="font-size:1.2rem;">📅</span>
            <span class="date"><?php echo date('d/m/Y', strtotime($inicio)); ?> al <?php echo date('d/m/Y', strtotime($fin)); ?></span>
        </div>
        <?php endif; ?>
    </div>

    <div class="card-total">
        <h2>Gastos en este periodo</h2>
        <p>TOTAL FACTURADO:</p>
        <div class="monto-grande"><?php echo number_format($total, 2, ',', '.'); ?>€</div>
    </div>

    <div class="card-tabla">
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Descripción / Concepto</th>
                    <th style="text-align:right;">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php echo $filas_html; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>