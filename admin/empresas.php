<?php
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
include '../php/conexion.php';
session_start();
if (!isset($_SESSION['usuario_id'])) { header("Location: ../index.html"); exit; }
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
            .then(reg => console.log('PWA detectada en Empresas'))
            .catch(err => console.error('Error PWA:', err));
        });
      }
    </script>
    <title>Empresas - Cerrajería Pinos</title>
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/trabajos_layout.css">
    <link rel="stylesheet" href="../css/formularios.css">
    <style>
        .container-empresas { padding: 20px; max-width: 1200px; margin: 0 auto; }
        .grid-gestion { display: grid; grid-template-columns: 350px 1fr; gap: 20px; }
        .card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .btn-ver-trabajos { background: #3498db; color: white; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 0.85rem; transition: background 0.3s; }
        .btn-ver-trabajos:hover { background: #2980b9; }
        .btn-eliminar { background: #e74c3c; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; transition: background 0.3s; }
        .btn-eliminar:hover { background: #c0392b; }
        .alerta-exito { background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #c3e6cb; }
    
        header { background-color: #2c3e50 !important; padding: 10px 15px !important; display: block !important; }
        .header-content { display: flex; align-items: center; justify-content: center; gap: 15px; margin-bottom: 10px; }
        .header-content h1 { margin: 0; font-size: 1.5rem; color: white; }
        .logo-img { height: 40px; width: auto; }
        .nav-container { display: flex; flex-wrap: wrap; justify-content: center; gap: 8px; width: 100%; }
        .btn-header { background: rgba(255, 255, 255, 0.1); color: white; text-decoration: none; padding: 8px 12px; border-radius: 5px; font-size: 0.85rem; font-weight: 500; transition: background 0.3s; border: 1px solid rgba(255, 255, 255, 0.1); }
        .btn-header:hover { background: rgba(255, 255, 255, 0.2); }

        /* --- Estilos para el nuevo Filtro de Facturación --- */
        .filtro-facturacion {
            background: #f1f4f7;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 15px;
            border: 1px solid #dcdfe3;
        }
        .filtro-facturacion label { font-size: 0.85rem; font-weight: bold; color: #34495e; }
        .input-fecha { padding: 6px; border: 1px solid #ccc; border-radius: 4px; }
        
        @media (max-width: 992px) {
            .grid-gestion { grid-template-columns: 1fr; }
            .filtro-facturacion { flex-direction: column; align-items: stretch; }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <a href="dashboard.php"><img src="../img/logo.png" alt="Logo" class="logo-img"></a>
            <h1>Empresas</h1>
        </div>
        <nav class="nav-container">
            <a href="dashboard.php" class="btn-header">🏠 Panel</a>
            <a href="impuestos.php" class="btn-header">📊 Impuestos</a>
            <a href="gestion_usuarios.php" class="btn-header">👥 Empleados</a>
            <a href="stock.php" class="btn-header">📦 Stock</a>
            <a href="gastos.php" class="btn-header">💸 Gastos </a>
            <a href="ingresos.php" class="btn-header">💰 Ingresos</a>
            <a href="ingresosb.php" class="btn-header">🤫 Extras</a>
            <a href="trabajos.php" class="btn-header">🛠️ Trabajos</a>
            <a href="plantillas.php" class="btn-header">🗒️ Plantillas</a>
            <a href="proveedores.php" class="btn-header"> 🚚 Proveedores</a>
            <a href="../php/logout.php" class="btn-header" style="background:#e74c3c;">Cerrar Sesion</a>
        </nav>
    </header>

    <main class="container-empresas">
        <?php if (isset($_GET['msj'])): ?>
            <div class="alerta-exito">✅ <?php echo htmlspecialchars($_GET['msj']); ?></div>
        <?php endif; ?>

        <div class="grid-gestion">
            <section class="card">
                <h3>Nueva Empresa</h3>
                <form action="../php/guardar_empresa.php" method="POST" class="mi-formulario">
                    <div class="input-group">
                        <label>Nombre de Empresa</label>
                        <input type="text" name="nombre" placeholder="Ej: MAPFRE" required>
                    </div>
                    <div class="input-group">
                        <label>CIF / NIF</label>
                        <input type="text" name="cif" placeholder="A12345678">
                    </div>
                    <div class="input-group">
                        <label>Teléfono Directo</label>
                        <input type="text" name="telefono" placeholder="968...">
                    </div>
                    <button type="submit" class="btn-guardar" style="width: 100%; margin-top: 15px;">💾 Guardar Empresa</button>
                </form>
            </section>

            <section class="card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h3>Empresas Colaboradoras</h3>
                </div>

                <div class="filtro-facturacion">
                    <div>
                        <label>Desde:</label>
                        <input type="date" id="f_inicio" class="input-fecha">
                    </div>
                    <div>
                        <label>Hasta:</label>
                        <input type="date" id="f_fin" class="input-fecha">
                    </div>
                    <p style="margin: 0; font-size: 0.8rem; color: #7f8c8d; max-width: 250px;">
                        Selecciona fechas para filtrar el total facturado al abrir la empresa.
                    </p>
                </div>

                <table style="width:100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f8f9fa;">
                            <th style="padding:12px; text-align:left;">Empresa</th>
                            <th style="padding:12px; text-align:left;">Contacto</th>
                            <th style="padding:12px; text-align:center;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT * FROM empresas ORDER BY nombre ASC";
                        $res = mysqli_query($conexion, $query);
                        if(mysqli_num_rows($res) > 0){
                            while($emp = mysqli_fetch_assoc($res)):
                        ?>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding:12px;">
                                <strong><?php echo htmlspecialchars($emp['nombre']); ?></strong><br>
                                <small style="color: #7f8c8d;"><?php echo htmlspecialchars($emp['cif']); ?></small>
                            </td>
                            <td style="padding:12px;"><?php echo htmlspecialchars($emp['telefono'] ?: '---'); ?></td>
                            <td style="padding:12px; text-align:center;">
                                <div style="display:flex; gap:8px; justify-content:center;">
                                    <button onclick="verTrabajosConFiltro('<?php echo urlencode($emp['nombre']); ?>')" class="btn-ver-trabajos" style="border:none; cursor:pointer;">📂 Ver Trabajos</button>
                                    <button onclick="eliminarEmpresa(<?php echo $emp['id']; ?>)" class="btn-eliminar">🗑️</button>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; } else { ?>
                            echo "<tr><td colspan='3' style='text-align:center; padding:30px; color:#95a5a6;'>No hay empresas registradas.</td></tr>";
                        <?php } ?>
                    </tbody>
                </table>
            </section>
        </div>
    </main>

    <script>
        // Función para enviar el nombre de la empresa y las fechas a la siguiente página
        function verTrabajosConFiltro(nombreEmpresa) {
            const inicio = document.getElementById('f_inicio').value;
            const fin = document.getElementById('f_fin').value;
            
            // Construimos la URL. Si hay fechas, las pasamos por GET
            let url = '../php/trabajo_empresas.php?nombre=' + nombreEmpresa;
            if(inicio) url += '&inicio=' + inicio;
            if(fin) url += '&fin=' + fin;
            
            window.location.href = url;
        }

        function eliminarEmpresa(id) {
            if(confirm('¿Estás seguro? Se borrará la empresa de la lista, pero los trabajos realizados NO se borrarán.')) {
                window.location.href = '../php/eliminar_empresa.php?id=' + id;
            }
        }

        if (window.location.search.includes('msj=')) {
            window.scrollTo(0, document.body.scrollHeight);
        }
    </script>
</body>
</html>