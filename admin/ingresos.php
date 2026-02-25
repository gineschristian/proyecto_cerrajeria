<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Verificación de Seguridad
if (!isset($_SESSION['usuario_id']) || strtolower(trim($_SESSION['rol'] ?? '')) !== 'admin') {
    header("Location: ../index.html");
    exit();
}

include '../php/conexion.php'; 

// --- CÁLCULO DE TOTALES PARA EVITAR SALTO VISUAL ---
$total_a = 0;
$total_b = 0;

$res_a = mysqli_query($conexion, "SELECT SUM(precio_total) as total FROM trabajos");
$row_a = mysqli_fetch_assoc($res_a);
$total_a = $row_a['total'] ?? 0;

$res_b = mysqli_query($conexion, "SELECT SUM(monto) as total FROM ingresos_b");
$row_b = mysqli_fetch_assoc($res_b);
$total_b = $row_b['total'] ?? 0;

$total_general = $total_a + $total_b;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="theme-color" content="#2c3e50">
    <title>Ingresos - Cerrajería Pinos</title>
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/formularios.css">
    <link rel="stylesheet" href="../css/trabajos_layout.css"> 
    <style>
        /* Ajustes Web */
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
        @media (max-width: 768px) {
            .tarjetas-resumen { flex-direction: column !important; }
            .header-tabla-dinamica { flex-direction: column; gap: 15px; }
            .filtros-fecha { width: 100%; display: flex; flex-wrap: wrap; gap: 5px; }
            .filtros-fecha input { flex: 1; }
        }
        .nav-container { display: flex; flex-wrap: wrap; gap: 8px; justify-content: center; margin-top: 10px; }
        .btn-pdf { background: #e74c3c; color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; font-weight: bold; display: flex; align-items: center; gap: 5px; }
        .print-only { display: none; }

        /* --- CONFIGURACIÓN DE IMPRESIÓN CORREGIDA --- */
        @media print {
            header, nav, .nav-container, .btn-pdf, .btn-filtro, .btn-cancelar, .btn-reset, 
            #modalEditarIngreso, td:last-child, th:last-child, .header-tabla-dinamica {
                display: none !important;
            }
            
            .print-only { 
                display: block !important; 
                text-align: center; 
                margin-bottom: 20px; 
                border-bottom: 2px solid #000;
                padding-bottom: 10px;
            }

            body { background: white !important; padding: 0; color: black; font-family: Arial, sans-serif; }
            
            .tabla-scroll-vertical { 
                overflow: visible !important; 
                height: auto !important; 
                max-height: none !important; 
                display: block !important;
            }

            .tarjetas-resumen { 
                display: flex !important; 
                flex-direction: row !important;
                justify-content: center !important;
                gap: 0 !important; 
                margin: 0 auto 25px auto !important;
                width: 70% !important; 
            }
            .card { 
                flex: 1;
                border: 1px solid #000 !important;
                box-shadow: none !important; 
                border-radius: 0 !important;
                padding: 10px !important;
                background: white !important;
                text-align: center;
            }
            .card h3 { font-size: 8pt !important; margin: 0; text-transform: uppercase; }
            .card p { font-size: 14pt !important; font-weight: bold !important; margin: 2px 0 !important; }
            .card small { font-size: 7pt !important; }

            .table-card { box-shadow: none; border: none; width: 100% !important; }
            .user-table { width: 100% !important; border-collapse: collapse !important; table-layout: auto !important; }
            .user-table th { background: #eee !important; border: 1px solid #000 !important; font-size: 9pt; }
            .user-table td { border: 1px solid #000 !important; font-size: 9pt; padding: 6px !important; }
        }
    </style>
</head>
<body>
    <div class="print-only">
        <h1 style="margin:0;">CERRAJERÍA PINOS</h1>
        <p style="margin:5px 0;">Libro de Ingresos | Emisión: <?php echo date('d/m/Y'); ?></p>
    </div>

    <header>
        <div class="header-content">
            <a href="dashboard.php">
            <img src="../img/logo.png" alt="Logo Cerrajeria Pinos" class="logo-img">
            </a>
            <h1>Ingresos</h1>
        </div>
        <nav class="nav-container">
            <a href="dashboard.php" class="btn-header">🏠 Panel</a>
            <a href="gastos.php" class="btn-header">💸 Gastos</a>
            <a href="gestion_usuarios.php" class="btn-header">👥 Empleados</a>
            <a href="impuestos.php" class="btn-header">📊 Impuestos</a>
            <a href="stock.php" class="btn-header">📦 Stock</a>
            <a href="ingresosb.php" class="btn-header">🤫 Extras</a>
            <a href="trabajos.php" class="btn-header">🛠️ Trabajos</a>
            <a href="plantillas.php" class="btn-header">🗒️ Plantillas</a>
            <a href="empresas.php" class="btn-header"> 🏢 Empresas</a>
            <a href="proveedores.php" class="btn-header"> 🚚 Proveedores</a>
            <a href="../php/logout.php" class="btn-header" style="background:#e74c3c;">Cerrar Sesión</a>
        </nav>
    </header>

    <main class="trabajos-layout" style="padding: 10px;"> 
        <div class="tarjetas-resumen">
            <div class="card oficial">
                <h3>Facturación (A)</h3>
                <p id="totalOficial"><?php echo number_format($total_a, 2, ',', '.'); ?>€</p>
                <small>Legal / Con IVA</small>
            </div>
            <div class="card extra">
                <h3>Extras (B)</h3>
                <p id="totalB"><?php echo number_format($total_b, 2, ',', '.'); ?>€</p>
                <small>Caja B / Sin Factura</small>
            </div>
            <div class="card total-caja">
                <h3>Caja Total</h3>
                <p id="totalGeneral"><?php echo number_format($total_general, 2, ',', '.'); ?>€</p>
                <small>Efectivo + Banco</small>
            </div>
        </div>

        <section class="table-card" style="width: 100%; margin-top: 20px; padding: 15px;">
            <div class="header-tabla-dinamica" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h2 style="border:none; margin:0;">Libro de Ingresos</h2>
                <div class="filtros-fecha" style="display:flex; gap:8px;">
                    <input type="date" id="fechaInicio" class="input-style-mini">
                    <input type="date" id="fechaFin" class="input-style-mini">
                    <button onclick="filtrarIngresos()" class="btn-filtro" style="padding: 8px 15px; background: #e74c3c; color: white; border: none; border-radius: 5px; cursor: pointer;">Filtrar</button>
                    <button onclick="limpiarFiltroIngresos()" class="btn-reset" style="padding: 8px 15px; background: #95a5a6; color: white; border: none; border-radius: 5px; cursor: pointer;">Reset</button>
                    <button onclick="window.print()" class="btn-pdf">📄 PDF</button>
                </div>
            </div>

            <div class="tabla-scroll-vertical">
                <table class="user-table" id="tablaIngresos"> 
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Concepto</th>
                            <th>Tipo</th>
                            <th>Monto</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="cuerpoTablaIngresos">
                        <?php include '../php/obtener_ingresos_totales.php'; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <div id="modalEditarIngreso" class="modal-overlay" style="display:none;">
        <div class="card-formulario modal-content">
            <h2 style="color: #27ae60; margin-bottom: 20px;">EDITAR INGRESO</h2>
            <form id="formEditarIngreso" class="mi-formulario">
                <input type="hidden" name="id" id="edit_ingreso_id">
                <input type="hidden" name="tabla" id="edit_ingreso_tabla">
                <div class="input-group">
                    <label>Concepto / Cliente</label>
                    <input type="text" name="concepto" id="edit_ingreso_concepto" required>
                </div>
                <div class="input-group">
                    <label>Monto (€)</label>
                    <input type="number" name="monto" id="edit_ingreso_monto" step="0.01" required>
                </div>
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" class="btn-guardar" style="background: #27ae60; flex: 1;">Actualizar</button>
                    <button type="button" onclick="cerrarModal('modalEditarIngreso')" class="btn-cancelar" style="background: #95a5a6; flex: 1;">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../js/ingresos.js"></script>
</body>
</html>