<?php
include 'conexion.php'; 
session_start();

// 1. Verificación de Seguridad Estricta
if (!isset($_SESSION['rol']) || strtolower(trim($_SESSION['rol'])) !== 'admin') { 
    header("Location: ../index.html"); 
    exit; 
}

// 2. Obtención de datos
$nombre_emp = $_GET['nombre'] ?? '';
$inicio = $_GET['inicio'] ?? ''; // NUEVO: Fecha inicio
$fin = $_GET['fin'] ?? '';       // NUEVO: Fecha fin

// Si intentan entrar sin un nombre de empresa, los devolvemos a la lista
if (empty($nombre_emp)) {
    header("Location: ../admin/empresas.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial - <?php echo htmlspecialchars($nombre_emp); ?></title>
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/trabajos_layout.css">
<style>
    .btn-volver {
            background: #3498db;
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 5px;
            font-size: 0.9rem;
        }
        .btn-pdf {
    background-color: #e74c3c; 
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-weight: bold;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: background 0.3s;
}

.btn-pdf:hover {
    background-color: #c0392b;
}

@media print {
    .btn-pdf, .btn-volver, header nav, .nav-container {
        display: none !important;
    }
}
</style>
</head>
<body>
    <header>
        <div class="header-content">
            <h1>📂 Historial: <?php echo htmlspecialchars($nombre_emp); ?></h1>
            <div style="display:flex; gap:10px; align-items:center;">
                <?php if(!empty($inicio) && !empty($fin)): ?>
                    <span style="color:white; background:rgba(0,0,0,0.3); padding:5px 10px; border-radius:4px; font-size:0.8rem;">
                        📅 <?php echo date('d/m/Y', strtotime($inicio)); ?> al <?php echo date('d/m/Y', strtotime($fin)); ?>
                    </span>
                <?php endif; ?>
                <button onclick="window.print()" class="btn-pdf">📄 Generar PDF / Imprimir</button>
                <a href="../admin/empresas.php" class="btn-volver">⬅️ Volver</a>
            </div>
        </div>
    </header>

    <main style="padding: 20px; max-width: 1200px; margin: 0 auto;">
        <div class="card" style="background:white; padding:20px; border-radius:10px; box-shadow:0 4px 6px rgba(0,0,0,0.1); margin-bottom:20px; display:flex; justify-content:space-between; align-items:center;">
            <h2 style="margin:0;">Trabajos Realizados</h2>
            <div style="text-align:right;">
                <span style="color:#7f8c8d;">TOTAL FACTURADO EN PERIODO:</span>
                <div id="totalFacturado" style="font-size:2rem; font-weight:800; color:#27ae60;">0,00€</div>
            </div>
        </div>

        <div class="card" style="background:white; padding:20px; border-radius:10px; box-shadow:0 4px 6px rgba(0,0,0,0.1);">
            <table style="width:100%; border-collapse: collapse;">
                <thead>
                    <tr style="background:#f8f9fa;">
                        <th style="padding:12px; text-align:left;">Fecha</th>
                        <th style="padding:12px; text-align:left;">Descripción</th>
                        <th style="padding:12px; text-align:left;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $nombre_limpio = mysqli_real_escape_string($conexion, $nombre_emp);
                    
                    // MODIFICACIÓN DE LA CONSULTA SQL:
                    $query = "SELECT * FROM trabajos WHERE nombre_cliente = '$nombre_limpio'";

                    // Si hay fechas, filtramos por ellas
                    if (!empty($inicio) && !empty($fin)) {
                        $inicioClean = mysqli_real_escape_string($conexion, $inicio);
                        $finClean = mysqli_real_escape_string($conexion, $fin);
                        $query .= " AND fecha BETWEEN '$inicioClean 00:00:00' AND '$finClean 23:59:59'";
                    }

                    $query .= " ORDER BY fecha DESC";
                    
                    $res = mysqli_query($conexion, $query);
                    $total_acumulado = 0;

                    if(mysqli_num_rows($res) > 0){
                        while($row = mysqli_fetch_assoc($res)){
                            $total_acumulado += $row['precio_total'];
                            echo "<tr style='border-bottom:1px solid #eee;'>
                                    <td style='padding:12px;'>".date('d/m/Y', strtotime($row['fecha']))."</td>
                                    <td style='padding:12px;'>".htmlspecialchars($row['descripcion'])."</td>
                                    <td style='padding:12px;'>".number_format($row['precio_total'], 2, ',', '.')."€</td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='3' style='padding:20px; text-align:center;'>No hay trabajos registrados en este rango de fechas.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </main>
    <script>
        // Actualizamos el total acumulado con el valor calculado por el filtro
        document.getElementById('totalFacturado').innerText = "<?php echo number_format($total_acumulado, 2, ',', '.'); ?>€";
    </script>
</body>
</html>