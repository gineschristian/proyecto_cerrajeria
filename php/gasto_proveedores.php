<?php
include 'conexion.php';
session_start();

// Seguridad: Solo Admin
if (!isset($_SESSION['rol']) || strtolower(trim($_SESSION['rol'])) !== 'admin') {
    exit("Acceso denegado");
}

$proveedor_nom = $_GET['nombre'] ?? '';

// Consultar los gastos de este proveedor
$p_limpio = mysqli_real_escape_string($conexion, $proveedor_nom);
$res = mysqli_query($conexion, "SELECT * FROM gastos WHERE proveedor = '$p_limpio' ORDER BY fecha DESC");

$total = 0;
$filas_html = "";

if (mysqli_num_rows($res) > 0) {
    while($row = mysqli_fetch_assoc($res)){
        $total += $row['monto'];
        $filas_html .= "
            <tr>
                <td>".date('d/m/Y', strtotime($row['fecha']))."</td>
                <td>".htmlspecialchars($row['concepto'])."</td>
                <td style='text-align:right;'>".number_format($row['monto'], 2)."€</td>
            </tr>";
    }
} else {
    $filas_html = "<tr><td colspan='3' style='text-align:center;'>No hay gastos registrados para este proveedor.</td></tr>";
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
        .monto-grande { color: #27ae60; font-size: 3rem; font-weight: 800; margin-top: 10px; }

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
        /* Estilo del botón */
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

/* CONFIGURACIÓN PARA EL PDF (Solo se activa al imprimir) */
@media print {
    header, .btn-pdf, .btn-volver {
        display: none !important; /* Oculta botones y header en el PDF */
    }
    body {
        background: white; /* Fondo blanco para ahorrar tinta */
    }
    .container {
        padding: 0;
        max-width: 100%;
    }
    .card-total, .card-tabla {
        box-shadow: none; /* Quitamos sombras para que quede más limpio en papel */
        border: 1px solid #eee;
    }
}
    </style>
</head>
<body>

<header>
    <div style="display:flex; align-items:center; gap:10px;">
        <span style="font-size:1.2rem;">📂</span>
        <h1 style="margin:0; font-size:1.2rem;">Historial: <?php echo htmlspecialchars($proveedor_nom); ?></h1>
    </div>
    <button onclick="window.print()" class="btn-pdf">📄 Generar PDF / Imprimir</button>
    <a href="../admin/proveedores.php" class="btn-volver">⬅️ Volver</a>
    
</header>

<div class="container">
    <div class="card-total">
        <h2>Gastos Realizados</h2>
        <p>TOTAL ACUMULADO:</p>
        <div class="monto-grande"><?php echo number_format($total, 2, ',', '.'); ?>€</div>
    </div>

    <div class="card-tabla">
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Descripción</th>
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