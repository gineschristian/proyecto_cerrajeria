<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Seguridad: Solo Admin
if (!isset($_SESSION['rol']) || strtolower(trim($_SESSION['rol'])) !== 'admin') {
    header("Location: ../index.html");
    exit();
}

include '../php/conexion.php';

// Capturamos fechas si existen para el cálculo del Gastado Total
$f_inicio = $_GET['inicio'] ?? '';
$f_fin = $_GET['fin'] ?? '';
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
            .then(reg => console.log('PWA lista en Proveedores'))
            .catch(err => console.error('Error PWA Proveedores:', err));
        });
      }
    </script>
    <title>Gestión de Proveedores - Cerrajería Pinos</title>
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/formularios.css">
    <link rel="stylesheet" href="../css/trabajos_layout.css">
    <style>
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

        .header-content h1 { 
            margin: 0; 
            font-size: 1.5rem; 
            color: white; 
        }

        .logo-img { 
            height: 40px; 
            width: auto; 
        }

        .nav-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 8px;
            width: 100%;
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

        /* Estilo para el filtro de fechas */
        .filtro-fechas-prov {
            background: #fdf2e9;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 15px;
            border: 1px solid #fad7a0;
        }
        .input-fecha { padding: 6px; border: 1px solid #ccc; border-radius: 4px; }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <a href="dashboard.php">
                <img src="../img/logo.png" alt="Logo Cerrajeria Pinos" class="logo-img">
            </a>
            <h1>Proveedores</h1>
        </div>
        <nav class="nav-container">
            <a href="dashboard.php" class="btn-header">🏠 Panel</a>
            <a href="impuestos.php" class="btn-header">📊 Impuestos</a>
            <a href="gastos.php" class="btn-header">💸 Gastos </a>
            <a href="gestion_usuarios.php" class="btn-header">👥 Empleados</a>
            <a href="stock.php" class="btn-header">📦 Stock</a>
            <a href="ingresos.php" class="btn-header">💰 Ingresos</a>
            <a href="empresas.php" class="btn-header"> 🏢 Empresas</a>
            <a href="ingresosb.php" class="btn-header">🤫 Extras</a>
            <a href="trabajos.php" class="btn-header">🛠️ Trabajos</a>
            <a href="plantillas.php" class="btn-header">🗒️ Plantillas</a>
            <a href="clientes.php" class="btn-header">🗂️ Clientes</a>
            <a href="../php/logout.php" class="btn-header" style="background:#e74c3c;">Cerrar Sesion</a>
        </nav>
    </header>

    <main class="trabajos-container-dual">
        <aside class="columna-formulario">
            <div class="card-formulario" style="border-top: 5px solid #f39c12;">
                <h2>Nuevo Proveedor</h2>
                <form action="../php/guardar_proveedor.php" method="POST" class="mi-formulario">
                    <div class="input-group">
                        <label>Nombre del Proveedor / Almacén</label>
                        <input type="text" name="nombre" placeholder="Ej: Leroy Merlin" required>
                    </div>
                    <div class="input-group">
                        <label>CIF / NIF (Opcional)</label>
                        <input type="text" name="cif" placeholder="B12345678">
                    </div>
                    <div class="input-group">
                        <label>Teléfono de Contacto</label>
                        <input type="text" name="telefono" placeholder="600000000">
                    </div>
                    <button type="submit" class="btn-guardar" style="background: #f39c12; width: 100%;">Añadir Proveedor</button>
                </form>
            </div>
        </aside>

        <section class="columna-tabla">
            <div class="table-card">
                <h2>Mis Proveedores</h2>
                
                <div class="filtro-fechas-prov">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <label>Desde:</label>
                        <input type="date" id="f_inicio" class="input-fecha" value="<?php echo $f_inicio; ?>">
                        <label>Hasta:</label>
                        <input type="date" id="f_fin" class="input-fecha" value="<?php echo $f_fin; ?>">
                        <button onclick="actualizarVistaGastos()" style="background:#f39c12; color:white; border:none; padding:7px 12px; border-radius:4px; cursor:pointer;">🔍 Filtrar Sumas</button>
                    </div>
                </div>

                <div class="tabla-scroll-vertical">
                    <table class="user-table">
                        <thead>
                            <tr>
                                <th>Proveedor</th>
                                <th>CIF</th>
                                <th>Gastado Total</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Preparamos el filtro SQL para el Gastado Total en la tabla
                            $filtroSQL = "";
                            if (!empty($f_inicio) && !empty($f_fin)) {
                                $filtroSQL = " AND fecha BETWEEN '$f_inicio' AND '$f_fin'";
                            }

                            $sql = "SELECT p.*, 
                                   (SELECT SUM(monto) FROM gastos WHERE proveedor = p.nombre $filtroSQL) as total_gastado 
                                   FROM proveedores p 
                                   ORDER BY p.nombre ASC";
                            
                            $resultado = mysqli_query($conexion, $sql);

                            while ($row = mysqli_fetch_assoc($resultado)) {
                                $total_formateado = number_format($row['total_gastado'] ?? 0, 2, ',', '.');
                                ?>
                                <tr>
                                    <td style='font-weight:bold;'><?php echo htmlspecialchars($row['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($row['cif']); ?></td>
                                    <td style='color:#c0392b; font-weight:bold;'>- <?php echo $total_formateado; ?>€</td>
                                    <td>
                                        <div style='display:flex; gap:10px;'>
                                            <button onclick="verHistorialProveedor('<?php echo urlencode($row['nombre']); ?>')" class='btn-header' style='background:#3498db; font-size:0.7rem; border:none; cursor:pointer;'>👁️ Ver Historial</button>
                                            <button onclick="eliminarProveedor(<?php echo $row['id']; ?>)" style='background:none; border:none; cursor:pointer;'>🗑️</button>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>

    <script>
    // Función para recargar la tabla principal aplicando el filtro a la columna "Gastado Total"
    function actualizarVistaGastos() {
        const inicio = document.getElementById('f_inicio').value;
        const fin = document.getElementById('f_fin').value;
        window.location.href = `proveedores.php?inicio=${inicio}&fin=${fin}`;
    }

    // Función para ir al historial filtrado
    function verHistorialProveedor(nombre) {
        const inicio = document.getElementById('f_inicio').value;
        const fin = document.getElementById('f_fin').value;
        let url = `../php/gasto_proveedores.php?nombre=${nombre}`;
        if(inicio && fin) url += `&inicio=${inicio}&fin=${fin}`;
        window.location.href = url;
    }

    function eliminarProveedor(id) {
        if (confirm('¿Estás seguro? Se perderá el vínculo con los gastos registrados a este nombre.')) {
            window.location.href = '../php/eliminar_proveedor.php?id=' + id;
        }
    }
    </script>
</body>
</html>