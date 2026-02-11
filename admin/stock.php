<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Verificamos que esté logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../index.html");
    exit();
}

// 2. DEFINIMOS LA VARIABLE QUE FALTA (Soluciona el error de la imagen)
$esAdmin = (isset($_SESSION['rol']) && strtolower(trim($_SESSION['rol'])) === 'admin');

// 3. Incluimos la conexión con la ruta correcta (subiendo un nivel)
include '../php/conexion.php'; 
?>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="theme-color" content="#2c3e50">
    <title>Stock - Cerrajería Pinos</title>
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/formularios.css">
    <style>
        .nav-container {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: center;
            margin-top: 10px;
        }
        @media (max-width: 768px) {
            header {
                flex-direction: column;
                padding: 15px;
            }
            .btn-header {
                font-size: 0.8rem;
                padding: 8px 12px;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <img src="../img/logo.png" alt="Logo Cerrajeria Pinos" class="logo-img">
            <h1>Stock - Cerrajería Pinos</h1>
        </div>
        <nav class="nav-container">
            <a href="dashboard.php" class="btn-header">🏠 Panel</a>
            <a href="trabajos.php" class="btn-header">🛠️ Trabajos</a>
            <a href="plantillas.php" class="btn-header">🗒️ Plantillas</a>
            
            <?php if ($esAdmin): ?>
                <a href="ingresos.php" class="btn-header">💰 Ingresos</a>
                <a href="gastos.php" class="btn-header">💸 Gastos</a>
                <a href="gestion_usuarios.php" class="btn-header">👥 Empleados </a>
                <a href="impuestos.php" class="btn-header">📊 Impuestos</a>
                <a href="ingresosb.php" class="btn-header">🤫 Extras</a>
            <?php endif; ?>

            <a href="../php/logout.php" class="btn-header" style="background:#e74c3c;">Cerrar Sesion</a>
        </nav>
    </header>

    <div class="search-container">
        <input type="text" id="buscadorStock" placeholder="Buscar por nombre de producto o categoría...">
    </div>

    <main class="stock-container">        
        
        <?php if ($esAdmin): ?>
        <section class="card-formulario">
            <h2>Añadir Nuevo Material</h2>
            <form id="formProducto" class="mi-formulario">
                <div class="input-group">
                    <label>Categoría</label>
                    <select name="categoria" class="input-style" required>
                        <option value="Bombines">Bombines</option>
                        <option value="Cerraduras">Cerraduras</option>
                        <option value="Escudos">Escudos</option>
                        <option value="Herramientas">Herramientas</option>
                        <option value="Mandos">Mandos</option>
                    </select>
                </div>
                <div class="input-group">
                    <label for="nombre">Producto</label>
                    <input type="text" id="nombre" name="nombre" placeholder="Ej: Cerradura FAC" required>
                </div>
                <div class="input-group">
                    <label for="cantidad">Cantidad Initial</label>
                    <input type="number" id="cantidad" name="cantidad" placeholder="0" required>
                </div>
                <div class="input-group">
                    <label for="imagen">Imagen del Producto</label>
                    <input type="file" id="imagen" name="imagen" accept="image/*">
                </div>
                <button type="submit" class="btn-guardar">Guardar Stock</button>
            </form>
        </section>
        <?php else: ?>
            <div class="card-formulario" style="text-align: center; border-left: 5px solid #3498db;">
                <p>📋 <strong>Modo Consulta:</strong> Como empleado puedes ver las existencias. El stock se descontará automáticamente al registrar un nuevo trabajo.</p>
            </div>
        <?php endif; ?>

        <div class="table-card">
            <h2>Control de Existencias</h2>
            <table id="tablaStock">
                <thead>
                    <tr>
                        <th>Imagen</th>
                        <th>Producto</th>
                        <th>Categoría</th> 
                        <th>Cantidad</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="cuerpoTabla">
                    <?php include '../php/obtener_producto.php'; ?>
                </tbody>
            </table>
        </div>
    </main>

    <?php if ($esAdmin): ?>
    <div id="modalEditarStock" class="modal-overlay" style="display:none;">
        <div class="card-formulario modal-content">
            <h2 style="color: #2980b9;">Editar Producto</h2>
            <form id="formEditarStock" class="mi-formulario">
                <input type="hidden" name="id" id="edit_id">
                <div class="input-group">
                    <label>Nombre del Producto</label>
                    <input type="text" name="nombre" id="edit_nombre" required>
                </div>
                <div class="input-group">
                    <label>Categoría</label>
                    <select name="categoria" id="edit_categoria" class="input-style">
                        <option value="Bombines">Bombines</option>
                        <option value="Cerraduras">Cerraduras</option>
                        <option value="Escudos">Escudos</option>
                        <option value="Herramientas">Herramientas</option>
                        <option value="Mandos">Mandos</option>
                    </select>
                </div>
                <div class="input-group">
                    <label>Cantidad en Stock</label>
                    <input type="number" name="cantidad" id="edit_cantidad" required>
                </div>
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" class="btn-guardar" style="background: #2980b9; flex: 1;">Actualizar Stock</button>
                    <button type="button" onclick="cerrarModal('modalEditarStock')" class="btn-cancelar" style="background: #95a5a6; flex: 1; border: none; color: white; border-radius: 5px; cursor: pointer;">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <button class="btn-ayuda" id="btnAyuda">?</button>
    
    <aside id="panelGuia" class="guia-sidebar">
        <div class="guia-header">
            <h3>Guía de Uso 🛠️</h3>
            <button class="btn-cerrar" id="btnCerrarGuia">&times;</button>
        </div>
        <div class="guia-body">
            <p><span class="dot verde"></span> <strong>Suficiente:</strong> 10 o más unidades.</p>
            <p><span class="dot naranja"></span> <strong>Bajo:</strong> Entre 1 y 9 unidades.</p>
            <p><span class="dot rojo"></span> <strong>Agotado:</strong> 0 unidades.</p>
            <hr>
            <?php if ($esAdmin): ?>
                <p>Usa el icono ✏️ para abrir el editor rápido de material.</p>
            <?php else: ?>
                <p>El stock se actualiza al registrar trabajos.</p>
            <?php endif; ?>
        </div>
    </aside>

    <script src="../js/stock.js"></script>
</body>
</html>