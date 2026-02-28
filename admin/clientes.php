<?php
session_start();
include '../php/conexion.php';

// Seguridad: Solo Admin
$esAdmin = (isset($_SESSION['rol']) && strtolower(trim($_SESSION['rol'])) === 'admin');
if (!$esAdmin) {
    header("Location: dashboard.php");
    exit();
}

// CONSULTA MAESTRA
$query = "SELECT 
            nombre_cliente, 
            MAX(localidad) as localidad, 
            COUNT(id) as total_trabajos, 
            SUM(precio_total) as inversion_total,
            MAX(fecha) as ultima_vez
          FROM trabajos 
          WHERE nombre_cliente IS NOT NULL AND nombre_cliente != ''
          GROUP BY nombre_cliente 
          ORDER BY nombre_cliente ASC";

$resultado = mysqli_query($conexion, $query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    
    <link rel="manifest" href="../manifest.json">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#2c3e50">
    <link rel="apple-touch-icon" href="../img/logo_pwa_192.png">

    <script>
      if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
          navigator.serviceWorker.register('../sw.js')
            .then(reg => console.log('PWA lista:', reg.scope))
            .catch(err => console.error('Error PWA:', err));
        });
      }
    </script>
    <title>Cartera de Clientes - Cerrajería Pinos</title>
    <link rel="stylesheet" href="--/css/header.css">
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/trabajos.css">
    <link rel="stylesheet" href="../css/formularios.css">  
    <style>
    /* --- Estilos Base --- */
    header { 
        background-color: #2c3e50 !important; 
        padding: 10px 15px !important;
        display: flex !important;
        position: sticky; /* Crítico para que el menú se posicione debajo */
    }

    .header-content {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .header-content h1 { 
        margin: 0; 
        font-size: 1.5rem; 
        color: white; 
    }

    .logo-img { 
        height: 40px; 
        width: auto; 
    }

    /* --- LÓGICA HAMBURGUESA --- */
    #menu-toggle { display: none; }

    .hamburger {
        display: none; /* Oculto en PC */
        color: white;
        font-size: 35px;
        cursor: pointer;
        padding: 10px;
        order: 2; /* A la derecha del contenido */
    }

    .nav-container {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 8px;
        order: 3; /* Debajo en móvil */
        width: 100%;
        margin-top: 10px;
    }

    .btn-header {
        background: rgba(255, 255, 255, 0.1);
        color: white;
        text-decoration: none;
        padding: 8px 12px;
        border-radius: 5px;
        font-size: 0.85rem;
        font-weight: 500;
        transition: background 0.3s;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .btn-header:hover {
        background: rgba(255, 255, 255, 0.2);
    }
    
    .btn-cerrar-header { background: #e74c3c !important; border: 1px solid #c0392b !important; }

    /* --- ESTILOS DE PÁGINA (Mantenidos) --- */
    .container { 
        background-color: rgba(255, 255, 255, 0.95); 
        padding: 30px; 
        border-radius: 15px; 
        margin: 50px auto !important;
        max-width: 1000px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.5);
    }

    .header-seccion { display: flex; align-items: center; gap: 15px; margin-bottom: 25px; }
    .search-bar { width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #ccc; margin-bottom: 20px; font-size: 16px; }
    .tabla-clientes { width: 100%; border-collapse: collapse; background: white; }
    .tabla-clientes th { background: #34495e; color: white; padding: 12px; text-align: left; }
    .tabla-clientes td { padding: 12px; border-bottom: 1px solid #eee; }
    .btn-historial { 
        background: #2980b9; color: white; padding: 6px 12px; border-radius: 4px; 
        text-decoration: none; font-size: 13px; font-weight: bold;
    }
    .badge { background: #e8f6f3; color: #16a085; padding: 4px 8px; border-radius: 12px; font-weight: bold; }

    /* --- RESPONSIVIDAD (Móvil) --- */
    @media (max-width: 768px) {
        .hamburger {
            display: block; /* Mostrar icono en móvil */
        }
        
        .nav-container {
            display: none; /* Ocultar botones por defecto en móvil */
            flex-direction: column;
            position: absolute;
            top: 100%;
            left: 0;
            background-color: #2c3e50;
            padding: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.2);
            box-sizing: border-box;
            gap: 10px;
            margin-top: 0;
        }

        /* Mostrar botones al hacer clic */
        #menu-toggle:checked ~ .nav-container {
            display: flex;
        }
        
        .btn-header {
            width: 100%;
            text-align: center;
            font-size: 0.9rem;
            padding: 12px;
        }
        
        .container { margin: 20px auto !important; padding: 15px; }
    }
</style>
</head>
<body>

<header>
    <div class="header-content">
        <a href="dashboard.php">
            <img src="../img/logo.png" alt="Logo" class="logo-img">
        </a>
        <h1>Clientes</h1>
    </div>
    <input type="checkbox" id="menu-toggle">
        <label for="menu-toggle" class="hamburger">&#9776;</label>
    <nav class="nav-container">
        <a href="dashboard.php" class="btn-header">🏠 Panel</a>
        <a href="stock.php" class="btn-header">📦 Stock</a>
        <a href="plantillas.php" class="btn-header">🗒️ Plantillas</a>
        <?php if ($esAdmin): ?>
            <a href="ingresos.php" class="btn-header">💰 Ingresos</a>
            <a href="gestion_usuarios.php" class="btn-header">👥 Empleados</a>
            <a href="gastos.php" class="btn-header">💸 Gastos</a>
            <a href="impuestos.php" class="btn-header">📊 Impuestos</a>
            <a href="ingresosb.php" class="btn-header">🤫 Extras</a>
            <a href="empresas.php" class="btn-header">🏢 Empresas</a>
            <a href="proveedores.php" class="btn-header"> 🚚 Proveedores</a>
        <?php endif; ?>
        <a href="trabajos.php" class="btn-header">🛠️ Trabajos</a>
        <a href="../php/logout.php" class="btn-header" style="background:#e74c3c;">Cerrar Sesión</a>
    </nav>
</header>

<div class="container">
    <div class="header-seccion">
        <span style="font-size: 35px;">🗂️</span>
        <div>
            <h1 style="margin:0;">Cartera de Clientes</h1>
            <p style="margin:0; color: #666;">Historial acumulado de intervenciones</p>
        </div>
    </div>

    <input type="text" id="buscador" class="search-bar" onkeyup="buscarCliente()" placeholder="Escribe nombre del cliente o localidad...">

    <table class="tabla-clientes" id="tablaClientes">
        <thead>
            <tr>
                <th>Cliente / Empresa</th>
                <th>Localidad</th>
                <th>Nº Trabajos</th>
                <th>Total Invertido</th>
                <th>Última Visita</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = mysqli_fetch_assoc($resultado)): ?>
            <tr>
                <td><strong><?php echo strtoupper(htmlspecialchars($row['nombre_cliente'])); ?></strong></td>
                <td>📍 <?php echo htmlspecialchars($row['localidad']); ?></td>
                <td><span class="badge"><?php echo $row['total_trabajos']; ?></span></td>
                <td style="font-weight: bold; color: #27ae60;"><?php echo number_format($row['inversion_total'], 2, ',', '.'); ?>€</td>
                <td style="color: #7f8c8d;"><?php echo date("d/m/Y", strtotime($row['ultima_vez'])); ?></td>
                <td>
                    <a href="../php/historial_cliente.php?nombre=<?php echo urlencode($row['nombre_cliente']); ?>" class="btn-historial">👁️ VER</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<script>
function buscarCliente() {
    let input = document.getElementById('buscador');
    let filter = input.value.toLowerCase();
    let table = document.getElementById('tablaClientes');
    let tr = table.getElementsByTagName('tr');

    for (let i = 1; i < tr.length; i++) {
        let texto = tr[i].innerText.toLowerCase();
        tr[i].style.display = texto.includes(filter) ? "" : "none";
    }
}
</script>

</body>
</html>