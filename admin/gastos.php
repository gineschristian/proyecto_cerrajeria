<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Verificamos si existe la sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../index.html");
    exit();
}

// 2. Verificamos el rol (Solo Admin puede gestionar gastos detallados)
$rol = isset($_SESSION['rol']) ? strtolower(trim($_SESSION['rol'])) : '';

if ($rol !== 'admin') {
    header("Location: dashboard.php?error=acceso_denegado");
    exit();   
}

include '../php/conexion.php'; 

// Pre-consulta de proveedores para usar en los selects
$query_prov = "SELECT id, nombre FROM proveedores ORDER BY nombre ASC";
$res_prov = mysqli_query($conexion, $query_prov);
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
    <title>Gastos - Cerrajería Pinos</title>
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/formularios.css">
    <link rel="stylesheet" href="../css/trabajos_layout.css">
    <style>
        /* --- ESTILOS PARA EL FILTRO DE CALENDARIO --- */
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
        .contenedor-filtros {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #fdf2f2;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 15px;
            border: 1px solid #f5b7b1;
            flex-wrap: wrap;
        }
        .filtro-fecha {
            padding: 6px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-family: inherit;
        }
        .btn-filtrar-fecha {
            background: #c0392b;
            color: white;
            border: none;
            padding: 7px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }
        .btn-limpiar-fecha {
            background: #95a5a6;
            color: white;
            border: none;
            padding: 7px 15px;
            border-radius: 5px;
            cursor: pointer;
        }

        /* --- ESTILOS WEB ORIGINALES --- */
        @media (max-width: 992px) {
            .trabajos-container-dual { display: flex; flex-direction: column; gap: 20px; padding: 10px; }
            .columna-formulario, .columna-tabla { width: 100% !important; }
            .header-tabla-dinamica { flex-direction: column; align-items: center; text-align: center; gap: 15px; }
            .contenedor-filtros { justify-content: center; }
        }
        .nav-container { display: flex; flex-wrap: wrap; gap: 8px; justify-content: center; margin-top: 10px; }
        .input-style { width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ddd; margin-top: 5px; }
        .btn-pdf { background: #e74c3c; color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; font-weight: bold; display: flex; align-items: center; gap: 5px; margin-left: 10px; }
        .print-only { display: none; }

        @media (max-width: 768px) {
            header { flex-direction: column; padding: 15px; }
            .btn-header { font-size: 0.8rem; padding: 8px 12px; }
        }

        /* ----------------------------------------------------------------
            CONFIGURACIÓN PDF: SIN ACCIÓN Y ANCHO COMPLETO
        ------------------------------------------------------------------- */
        @media print {
            header, nav, .nav-container, .columna-formulario, .btn-pdf, .contenedor-filtros,
            #modalEditarGasto, .btn-guardar, .btn-cancelar, 
            th:last-child, td:last-child {
                display: none !important;
            }

            body { background: white !important; margin: 0 !important; padding: 0 !important; }
            .trabajos-container-dual { display: block !important; width: 100% !important; margin: 0 !important; padding: 0 !important; }
            .columna-tabla { width: 100% !important; max-width: 100% !important; padding: 0 !important; margin: 0 !important; }
            .table-card { box-shadow: none !important; border: none !important; width: 100% !important; padding: 0 !important; }

            .user-table { display: table !important; width: 100% !important; border-collapse: collapse !important; table-layout: auto !important; }
            .user-table thead { display: table-header-group !important; }
            .user-table tr { display: table-row !important; }
            .user-table th, .user-table td { display: table-cell !important; border: 1px solid #000 !important; padding: 6px !important; font-size: 9pt !important; text-align: left !important; }
            .user-table td::before { content: none !important; display: none !important; }

            .print-only { display: block !important; text-align: center; margin-bottom: 15px; border-bottom: 1px solid #000; padding-bottom: 10px; }
            .header-tabla-dinamica { display: flex !important; justify-content: center !important; margin-bottom: 15px !important; }
            .header-tabla-dinamica h2 { display: none !important; }
            .contador-total { background: #f5b7b1 !important; border: 1px solid #000 !important; width: auto !important; min-width: 250px; padding: 8px 20px !important; border-radius: 0 !important; }
            .tabla-scroll-vertical { overflow: visible !important; height: auto !important; }
        }
    </style>
</head>
<body>
    <div class="print-only">
        <h1 style="margin:0;">CERRAJERÍA PINOS</h1>
        <p style="margin:5px 0;">Informe Detallado de Gastos | Fecha: <?php echo date('d/m/Y'); ?></p>
    </div>

    <header>
        <div class="header-content">
            <a href="dashboard.php">
            <img src="../img/logo.png" alt="Logo Cerrajeria Pinos" class="logo-img">
            </a>
            <h1>Gastos</h1>
        </div>
        <nav class="nav-container">
            <a href="dashboard.php" class="btn-header">🏠 Panel</a>
            <a href="impuestos.php" class="btn-header">📊 Impuestos</a>
            <a href="gestion_usuarios.php" class="btn-header">👥 Empleados</a>
            <a href="stock.php" class="btn-header">📦 Stock</a>
            <a href="ingresos.php" class="btn-header">💰 Ingresos</a>
            <a href="ingresosb.php" class="btn-header">🤫 Extras</a>
            <a href="trabajos.php" class="btn-header">🛠️ Trabajos</a>
            <a href="plantillas.php" class="btn-header">🗒️ Plantillas</a>
            <a href="empresas.php" class="btn-header"> 🏢 Empresas</a>
            <a href="proveedores.php" class="btn-header"> 🚚 Proveedores</a>
            <a href="clientes.php" class="btn-header">🗂️ Clientes</a>
            <a href="../php/logout.php" class="btn-header" style="background:#e74c3c;">Cerrar Sesión</a>
        </nav>
    </header>

    <main class="trabajos-container-dual"> 
        <aside class="columna-formulario">
            <div class="card-formulario" style="border-top: 5px solid #c0392b;">
                <h2>Registrar Compra / Gasto</h2>
                <form id="formGasto" class="mi-formulario">
                    <div class="input-group">
                        <label>Seleccionar Proveedor</label>
                        <select name="proveedor" id="gasto_proveedor" class="input-style" required>
                            <option value="">-- Seleccione un proveedor --</option>
                            <?php 
                            mysqli_data_seek($res_prov, 0);
                            while($p = mysqli_fetch_assoc($res_prov)): ?>
                                <option value="<?php echo htmlspecialchars($p['nombre']); ?>">
                                    <?php echo htmlspecialchars($p['nombre']); ?>
                                </option>
                            <?php endwhile; ?>
                            <option value="Otros">Varios / Otros</option>
                        </select>
                    </div>
                    <div class="input-group">
                        <label>Concepto / Detalle</label>
                        <input type="text" name="concepto" placeholder="Ej: Compra de 20 bombines" required>
                    </div>
                    <div class="input-group">
                        <label>Categoría</label>
                        <select name="categoria" class="input-style">
                            <option value="Material">Material / Stock</option>
                            <option value="Herramientas">Herramientas</option>
                            <option value="Gasolina">Gasolina / Vehículo</option>
                            <option value="Local">Alquiler / Suministros</option>
                            <option value="Otros">Otros</option>
                        </select>
                    </div>
                    <div class="input-group">
                        <label>Monto Total (€)</label>
                        <input type="number" name="monto" step="0.01" placeholder="0.00" required>
                    </div>
                    <div class="input-group">
                        <label for="gasto_factura">¿Tiene factura oficial?</label>
                        <select name="con_factura" id="gasto_factura" class="input-style" required>
                            <option value="0">No (Ticket/Recibo B)</option>
                            <option value="1" selected>Sí (Factura con IVA)</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-guardar" style="background: #c0392b; width: 100%; margin-top: 10px;">💾 Registrar Gasto</button>
                </form>
            </div>
        </aside>

        <section class="columna-tabla">
            <div class="table-card">
                
                <div class="contenedor-filtros">
                    <div style="display:flex; align-items:center; gap:5px;">
                        <label style="font-weight:bold; font-size:0.9rem;">Desde:</label>
                        <input type="date" id="fechaInicio" class="filtro-fecha">
                    </div>
                    <div style="display:flex; align-items:center; gap:5px;">
                        <label style="font-weight:bold; font-size:0.9rem;">Hasta:</label>
                        <input type="date" id="fechaFin" class="filtro-fecha">
                    </div>
                    <button type="button" class="btn-filtrar-fecha" onclick="filtrarGastosFecha()">Filtrar</button>
                    <button type="button" class="btn-limpiar-fecha" onclick="limpiarFiltroFecha()">Reset</button>
                </div>

                <div class="header-tabla-dinamica" style="display:flex; justify-content: space-between; align-items: center;">
                    <h2 style="margin:0; border:none;">Historial de Gastos</h2>
                    <div style="display:flex; align-items:center;">
                        <div class="contador-total" style="background: #f5b7b1; padding: 10px 20px; border-radius: 10px;">
                            <span class="etiqueta" style="font-weight:bold;">Total Gastado:</span>
                            <span class="cifra" id="totalGastos" style="color: black; font-weight:bold; font-size: 1.2rem;">0.00€</span>
                        </div>
                        <button onclick="window.print()" class="btn-pdf">📄 PDF</button>
                    </div>
                </div>

                <div class="tabla-scroll-vertical">
                    <table class="user-table" id="tablaGastos"> 
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Proveedor</th>
                                <th>Concepto</th>
                                <th>Categoría</th>
                                <th>Factura</th>
                                <th>Monto</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody id="cuerpoTablaGastos">
                            <?php include '../php/obtener_gastos.php'; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>

    <div id="modalEditarGasto" class="modal-overlay" style="display:none;">
        <div class="card-formulario modal-content">
            <h2 style="color: #c0392b; margin-bottom: 20px;">Editar Gasto</h2>
            <form id="formEditarGasto" class="mi-formulario">
                <input type="hidden" name="id" id="edit_id">
                <div class="input-group">
                    <label>Proveedor</label>
                    <select name="proveedor" id="edit_proveedor" class="input-style">
                        <?php 
                        mysqli_data_seek($res_prov, 0);
                        while($p = mysqli_fetch_assoc($res_prov)): ?>
                            <option value="<?php echo htmlspecialchars($p['nombre']); ?>">
                                <?php echo htmlspecialchars($p['nombre']); ?>
                            </option>
                        <?php endwhile; ?>
                        <option value="Otros">Varios / Otros</option>
                    </select>
                </div>
                <div class="input-group">
                    <label>Concepto / Detalle</label>
                    <input type="text" name="concepto" id="edit_concepto" required>
                </div>
                <div class="input-group">
                    <label>Categoría</label>
                    <select name="categoria" id="edit_categoria" class="input-style">
                        <option value="Material">Material / Stock</option>
                        <option value="Herramientas">Herramientas</option>
                        <option value="Gasolina">Gasolina / Vehículo</option>
                        <option value="Local">Alquiler / Suministros</option>
                        <option value="Otros">Otros</option>
                    </select>
                </div>
                <div class="input-group">
                    <label>Monto (€)</label>
                    <input type="number" name="monto" id="edit_monto" step="0.01" required>
                </div>
                <div class="input-group">
                    <label>Factura</label>
                    <select name="con_factura" id="edit_factura" class="input-style">
                        <option value="1">Sí (Oficial)</option>
                        <option value="0">No (Ticket)</option>
                    </select>
                </div>
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" class="btn-guardar" style="background: #c0392b; flex: 1;">Guardar Cambios</button>
                    <button type="button" onclick="document.getElementById('modalEditarGasto').style.display='none'" class="btn-cancelar" style="background: #95a5a6; flex: 1; border: none; color: white; border-radius: 5px; cursor: pointer;">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../js/gastos.js"></script>
</body>
</html>