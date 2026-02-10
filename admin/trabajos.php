<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../public/login.html");
    exit();
}
$esAdmin = ($_SESSION['rol'] === 'admin');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="theme-color" content="#2c3e50">
    <title>Trabajos - Cerrajería Pinos</title>
    <link rel="stylesheet" href="../css/main.css?v=1.2">
    <link rel="stylesheet" href="../css/formularios.css?v=1.2">
    <link rel="stylesheet" href="../css/trabajos_layout.css?v=1.2">
    <style>
        /* AJUSTES CRÍTICOS PARA MÓVIL */
        header { background-color: #2c3e50 !important; }

        .trabajos-container-dual {
            display: flex;
            flex-wrap: wrap; /* Permite que las columnas bajen en móvil */
            gap: 20px;
            padding: 15px;
        }

        .columna-formulario, .columna-tabla {
            flex: 1;
            min-width: 320px; /* Ancho mínimo para que no se aplaste */
        }

        @media (max-width: 850px) {
            .trabajos-container-dual {
                flex-direction: column; /* Una columna en móvil/tablet vertical */
            }
            .nav-container {
                display: flex;
                overflow-x: auto; /* Menú deslizable si no cabe */
                padding-bottom: 10px;
                gap: 10px;
            }
            .btn-header {
                white-space: nowrap; /* Que no se corten los textos del menú */
            }
            .filtros-fecha {
                flex-direction: column;
                gap: 10px;
            }
            .input-filtro { width: 100%; }
            .btn-filtro { width: 100%; padding: 12px; }
        }

        /* Estilo para los radio buttons en móvil */
        .radio-group {
            display: flex;
            gap: 20px;
            margin-top: 10px;
        }
        .radio-label {
            background: #f8f9fa;
            padding: 10px 15px;
            border-radius: 8px;
            border: 1px solid #ddd;
            flex: 1;
            text-align: center;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <img src="../img/logo.png" alt="Logo Cerrajeria Pinos" class="logo-img">
            <h1>Cerrajería Pinos</h1>
        </div>
        <nav class="nav-container">
            <a href="dashboard.php" class="btn-header">🏠 Panel</a>
            <a href="stock.php" class="btn-header">📦 Stock</a>
            <a href="trabajos.php" class="btn-header">🛠️ Trabajos</a>
            <?php if ($esAdmin): ?>
                <a href="ingresos.php" class="btn-header">💰 Ingresos</a>
                <a href="gastos.php" class="btn-header">💸 Gastos</a>
                <a href="impuestos.php" class="btn-header">📊 Impuestos</a>
            <?php endif; ?>
            <a href="../php/logout.php" class="btn-header" style="background:#e74c3c;">Cerrar Sesion</a>
        </nav>
    </header>

    <main class="trabajos-container-dual"> 
        
        <aside class="columna-formulario">
            <div class="card-formulario">
                <h2 style="border-bottom: 2px solid #2c3e50; padding-bottom: 10px;">Nuevo Trabajo</h2>
                <form id="formTrabajo" class="mi-formulario">
                    <div class="input-group">
                        <label>Cliente / Dirección</label>
                        <input type="text" name="cliente" placeholder="Ej: Calle Mayor 5, 2B" required>
                    </div>

                    <div class="input-group">
                        <label>Descripción</label>
                        <textarea name="descripcion" placeholder="Apertura, cambio de bombín..." rows="3"></textarea>
                    </div>

                    <div class="zona-material" style="background: #ebf2f7; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                        <label><strong>Material del stock</strong></label>
                        <select name="producto_id" id="listaProductos" class="input-style" style="width: 100%; margin-top:5px;">
                            <option value="">-- Solo mano de obra --</option>
                            <?php 
                            include '../php/conexion.php'; 
                            $res = mysqli_query($conexion, "SELECT id, nombre, cantidad FROM productos WHERE cantidad > 0 ORDER BY nombre ASC");
                            while($p = mysqli_fetch_assoc($res)) {
                                echo "<option value='{$p['id']}'>{$p['nombre']} ({$p['cantidad']})</option>";
                            }
                            ?>
                        </select>
                        <label style="margin-top:10px; display:block;">Cantidad utilizada</label>
                        <input type="number" name="cantidad_gastada" value="0" min="0" style="width: 100%;">
                    </div>

                    <div class="input-group">
                        <label>Precio Cobrado (€)</label>
                        <input type="number" name="precio_total" step="0.01" placeholder="0.00" required style="font-size: 1.2rem; font-weight: bold;">
                    </div>

                    <div class="input-group">
                        <label><strong>¿Lleva factura oficial?</strong></label>
                        <div class="radio-group">
                            <label class="radio-label"><input type="radio" name="factura" value="1"> SÍ</label>
                            <label class="radio-label"><input type="radio" name="factura" value="0" checked> NO</label>
                        </div>
                    </div>

                    <button type="submit" class="btn-guardar" style="width: 100%; padding: 15px; font-size: 1.1rem; background: #27ae60;">💾 Guardar Trabajo</button>
                </form>
            </div>
        </aside>

        <section class="columna-tabla">
            <div class="table-card">
                <div class="header-tabla-dinamica" style="padding: 10px;">
                    <div class="filtros-fecha" style="display: flex; align-items: flex-end; gap: 10px;">
                        <div class="input-filtro">
                            <label>Desde:</label>
                            <input type="date" id="fechaInicio" class="input-style-mini">
                        </div>
                        <div class="input-filtro">
                            <label>Hasta:</label>
                            <input type="date" id="fechaFin" class="input-style-mini">
                        </div>
                        <button type="button" onclick="filtrarTrabajos()" class="btn-filtro" style="background:#34495e;">🔍</button>
                    </div>
                    
                    <?php if ($esAdmin): ?>
                    <div class="contador-total" style="margin-top: 15px; text-align: right;">
                        <span style="font-size: 0.9rem; color: #7f8c8d;">Total Periodo:</span>
                        <div style="font-size: 1.5rem; font-weight: bold; color: #2c3e50;" id="totalFacturado">0.00€</div>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="tabla-scroll-vertical" style="overflow-x: auto;">
                    <table id="tablaTrabajosResponsive">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Cliente</th>
                                <th>Descripción</th>
                                <th>Fact</th>
                                <th>Total</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody id="cuerpoTablaTrabajos">
                            <?php include '../php/obtener_trabajos.php'; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>

    <script src="../js/trabajos.js?v=1.2"></script>
</body>
</html>