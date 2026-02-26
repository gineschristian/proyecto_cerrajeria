<?php
session_start();
// Ajustamos la ruta de conexión porque ahora estamos dentro de /php/
include 'conexion.php'; 

$esAdmin = (isset($_SESSION['rol']) && strtolower(trim($_SESSION['rol'])) === 'admin');
if (!$esAdmin) {
    header("Location: ../admin/dashboard.php");
    exit();
}

$nombre_cliente = isset($_GET['nombre']) ? $_GET['nombre'] : '';
if (empty($nombre_cliente)) { 
    die("Error: No se recibió el nombre del cliente."); 
}

$nombre_limpio = mysqli_real_escape_string($conexion, $nombre_cliente);

// Consulta para obtener los trabajos de ese cliente
$query = "SELECT * FROM trabajos WHERE nombre_cliente = '$nombre_limpio' ORDER BY fecha DESC";
$resultado = mysqli_query($conexion, $query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial - <?php echo htmlspecialchars($nombre_cliente); ?></title>
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/trabajos_layout.css">
    <style>
        /* --- HEADER OFICIAL (Copiado de trabajos.php) --- */
        header { 
            background-color: #2c3e50 !important; 
            padding: 10px 15px !important;
            display: block !important;
        }
        .header-content {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            margin-bottom: 10px;
        }
        .header-content h1 { margin: 0; font-size: 1.5rem; color: white; }
        .logo-img { height: 40px; width: auto; }
        .nav-container { display: flex; flex-wrap: wrap; justify-content: center; gap: 8px; width: 100%; }
        .btn-header {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 5px;
            font-size: 0.85rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* --- DISEÑO DE LA TABLA --- */
        .container-historial {
            max-width: 1100px;
            margin: 30px auto;
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .tabla-estilo {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .tabla-estilo th {
            background: #f8f9fa;
            padding: 12px;
            text-align: left;
            border-bottom: 2px solid #3498db;
            color: #2c3e50;
        }
        .tabla-estilo td {
            padding: 15px 12px;
            border-bottom: 1px solid #eee;
        }
        .precio-total {
            font-weight: bold;
            color: #27ae60;
            font-size: 1.1rem;
        }
        .btn-volver-atras {
            display: inline-block;
            margin-bottom: 15px;
            color: #3498db;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
<body>

<header>
    <div class="header-content">
        <a href="../admin/dashboard.php">
            <img src="../img/logo.png" alt="Logo" class="logo-img">
        </a>
        <h1>Historial de Trabajo</h1>
    </div>
    <nav class="nav-container">
            <a href="../admin/dashboard.php" class="btn-header">🏠 Panel</a>
            <a href="../admin/stock.php" class="btn-header">📦 Stock</a>
            <a href="../admin/plantillas.php" class="btn-header">🗒️ Plantillas</a>
            <?php if ($esAdmin): ?>
                <a href="../admin/ingresos.php" class="btn-header">💰 Ingresos</a>
                <a href="../admin/gestion_usuarios.php" class="btn-header">👥 Empleados</a>
                <a href="../admin/gastos.php" class="btn-header">💸 Gastos</a>
                <a href="../admin/impuestos.php" class="btn-header">📊 Impuestos</a>
                <a href="../admin/ingresosb.php" class="btn-header">🤫 Extras</a>
                <a href="../admin/empresas.php" class="btn-header"> 🏢 Empresas</a>
            <?php endif; ?>
            <a href="logout.php" class="btn-header" style="background:#e74c3c;">Cerrar Sesión</a>
        </nav>
</header>

<div class="container-historial">
    <a href="../admin/clientes.php" class="btn-volver-atras">← Volver a la lista de clientes</a>
    
    <div style="border-left: 5px solid #3498db; padding-left: 15px; margin-bottom: 20px;">
        <h2 style="margin:0; text-transform: uppercase; color: #2c3e50;">
            <?php echo htmlspecialchars($nombre_cliente); ?>
        </h2>
        <p style="margin:0; color: #7f8c8d;">Registro histórico de servicios realizados</p>
    </div>

    <table class="tabla-estilo">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Ubicación</th>
                <th>Descripción / Detalles</th>
                <th style="text-align:right;">Importe</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if (mysqli_num_rows($resultado) > 0) {
                while($row = mysqli_fetch_assoc($resultado)) {
                    // Verificamos si el campo se llama description o descripcion
                    $desc = isset($row['description']) ? $row['description'] : (isset($row['descripcion']) ? $row['descripcion'] : 'N/A');
                    
                    echo "<tr>";
                    echo "<td style='white-space:nowrap;'><strong>" . date("d/m/Y", strtotime($row['fecha'])) . "</strong></td>";
                    echo "<td>
                            <span style='color:#2c3e50; font-weight:600;'>📍 " . htmlspecialchars($row['localidad']) . "</span><br>
                            <small style='color:#666;'>" . htmlspecialchars($row['cliente']) . "</small>
                          </td>";
                    echo "<td style='font-size: 0.9rem; color: #444;'>" . nl2br(htmlspecialchars($desc)) . "</td>";
                    echo "<td style='text-align:right;' class='precio-total'>" . number_format($row['precio_total'], 2, ',', '.') . "€</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='4' style='text-align:center; padding:30px; color:#999;'>No hay trabajos registrados para este cliente.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

</body>
</html>