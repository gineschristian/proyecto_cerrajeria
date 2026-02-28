<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Verificamos si existe la sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../index.html");
    exit();
}

// 2. Verificamos el rol
$rol = isset($_SESSION['rol']) ? strtolower(trim($_SESSION['rol'])) : '';

if ($rol !== 'admin') {
    header("Location: dashboard.php?error=acceso_denegado");
    exit();
}

include '../php/conexion.php'; 

// --- CÁLCULO DE ACUMULADO INICIAL ---
$res_total = mysqli_query($conexion, "SELECT SUM(monto) as total FROM ingresos_b");
$row_total = mysqli_fetch_assoc($res_total);
$acumulado_b = $row_total['total'] ?? 0;
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
            .then(reg => console.log('PWA detectada en Ingresos B'))
            .catch(err => console.error('Error PWA Ingresos B:', err));
        });
      }
    </script>
    <title>Extras (Caja B) - Cerrajería Pinos</title>
    <link rel="stylesheet" href="../css/header.css">
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/formularios.css">
    <link rel="stylesheet" href="../css/trabajos_layout.css">
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
        order: 2;
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
    }

    /* --- CONFIGURACIÓN DE IMPRESIÓN (Mantenida) --- */
    @media print {
        header, 
        .nav-container, 
        .hamburger,
        .columna-formulario, 
        .filtros-container, 
        .btn-header, 
        .btn-editar, 
        .btn-eliminar, 
        button {
            display: none !important;
        }

        body {
            background: white !important;
            margin: 0;
            padding: 0;
        }

        .trabajos-container-dual {
            display: block !important;
            width: 100% !important;
        }

        .columna-tabla {
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .header-tabla-dinamica {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            border-bottom: 2px solid #e67e22 !important;
            margin-bottom: 20px !important;
            padding-bottom: 10px !important;
        }

        .contador-total {
            background: #f8f9fa !important;
            border: 1px solid #ddd !important;
            padding: 10px 20px !important;
        }

        .cifra {
            color: black !important;
            font-weight: bold !important;
        }

        .table-card {
            box-shadow: none !important;
            border: none !important;
        }

        table {
            width: 100% !important;
            border-collapse: collapse !important;
        }

        th, td {
            border: 1px solid #ddd !important;
            padding: 8px !important;
            text-align: left !important;
            font-size: 10pt !important;
        }

        th {
            background-color: #f2f2f2 !important;
            color: black !important;
        }

        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
    }
</style>
</head>
<body>
    <header>
        <div class="header-content">
            <a href="dashboard.php">
            <img src="../img/logo.png" alt="Logo Cerrajeria Pinos" class="logo-img">
            </a>
            <h1>Control de Extras</h1>
        </div>
        <input type="checkbox" id="menu-toggle">
        <label for="menu-toggle" class="hamburger">&#9776;</label>
        <nav class="nav-container">
            <a href="dashboard.php" class="btn-header" > 🏠 Panel</a>
            <a href="stock.php" class="btn-header" > 📦 Stock</a>
            <a href="impuestos.php" class="btn-header" >📊 Impuestos</a>
            <a href="gestion_usuarios.php" class="btn-header">👥 Empleados </a>
            <a href="ingresos.php" class="btn-header" >💰 Ingresos</a>
            <a href="trabajos.php" class="btn-header" >🛠️ Trabajos</a>
            <a href="plantillas.php" class="btn-header">🗒️ Plantillas</a>
            <a href="gastos.php" class="btn-header" > 💸 Gastos</a>
            <a href="empresas.php" class="btn-header"> 🏢 Empresas</a>
            <a href="proveedores.php" class="btn-header"> 🚚 Proveedores</a>
            <a href="clientes.php" class="btn-header">🗂️ Clientes</a>
            <a href="../php/logout.php" class="btn-header" style="background:#e74c3c;">Cerrar Sesion</a>
        </nav>
    </header>

    <main class="trabajos-container-dual"> 
        
        <aside class="columna-formulario">
            <div class="card-formulario" style="border-top: 5px solid #e67e22;">
                <h2>Añadir Ingreso Extra</h2>
                <p style="font-size: 0.8em; color: #666; margin-bottom: 15px;">Use esto para ventas directas o ingresos que no sean trabajos de cerrajería.</p>
                
                <form id="formExtraManual" class="mi-formulario">
                    <div class="input-group">
                        <label>Concepto</label>
                        <input type="text" name="concepto" placeholder="Ej: Venta mando garaje" required>
                    </div>

                    <div class="input-group">
                        <label>Monto (€)</label>
                        <input type="number" name="monto" step="0.01" placeholder="0.00" required>
                    </div>

                    <button type="submit" class="btn-guardar" style="background: #e67e22;">Guardar en Caja B</button>
                </form>
            </div>
        </aside>

        <section class="columna-tabla">
    <div class="filtros-container" style="margin-bottom: 15px; display: flex; gap: 10px; align-items: center; background: white; padding: 15px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <input type="date" id="fechaInicioB" class="input-style-mini" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
        <input type="date" id="fechaFinB" class="input-style-mini" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
        <button onclick="filtrarExtras()" class="btn-filtro" style="padding: 8px 15px; background: #e67e22; color: white; border: none; border-radius: 5px; cursor: pointer;">Filtrar</button>
        <button onclick="limpiarFiltroExtras()" class="btn-reset" style="padding: 8px 15px; background: #95a5a6; color: white; border: none; border-radius: 5px; cursor: pointer;">Reset</button>
        <button onclick="window.print()" class="btn-pdf" style="padding: 8px 15px; background: #34495e; color: white; border: none; border-radius: 5px; cursor: pointer;">📄 PDF</button>
    </div>

    <div class="table-card">
        <div class="header-tabla-dinamica">
            <h2>Listado de Dinero Extra</h2>
            <div class="contador-total" style="background: #e67e22;">
                <span class="etiqueta">Acumulado B:</span>
                <span class="cifra" id="totalExtras" style="color: black;"><?php echo number_format($acumulado_b, 2, ',', '.'); ?>€</span>
            </div>
        </div>

                <div class="tabla-scroll-vertical">
                    <table>
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Concepto</th>
                                <th>Monto</th>
                                <th>Origen</th>
                            </tr>
                        </thead>
                        <tbody id="cuerpoTablaExtras">
                            <?php include '../php/obtener_extras.php'; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>
<div id="modalEditarExtra" class="modal-overlay" style="display:none;">
    <div class="card-formulario modal-content">
        <h2 style="color: #e67e22;">Editar Ingreso B</h2>
        <form id="formEditarExtra" class="mi-formulario">
            <input type="hidden" name="id" id="edit_extra_id">
            <div class="input-group">
                <label>Concepto</label>
                <input type="text" name="concepto" id="edit_extra_concepto" required>
            </div>
            <div class="input-group">
                <label>Monto (€)</label>
                <input type="number" name="monto" id="edit_extra_monto" step="0.01" required>
            </div>
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="btn-guardar" style="background: #e67e22; flex: 1;">Actualizar</button>
                <button type="button" onclick="cerrarModal('modalEditarExtra')" class="btn-cancelar" style="background: #95a5a6; flex: 1; border: none; color: white; border-radius: 5px; cursor: pointer;">Cancelar</button>
            </div>
        </form>
    </div>
</div>
    <script src="../js/extras.js"></script>
</body>
</html>