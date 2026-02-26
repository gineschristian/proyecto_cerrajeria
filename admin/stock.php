<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Verificamos que esté logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../index.html");
    exit();
}

// 2. Variable de control
$esAdmin = (isset($_SESSION['rol']) && strtolower(trim($_SESSION['rol'])) === 'admin');

// 3. Incluimos la conexión
include '../php/conexion.php'; 

// 4. Obtenemos los usuarios para los filtros y la tabla
$usuarios_list = [];
$res_u = mysqli_query($conexion, "SELECT id, nombre FROM usuarios ORDER BY id ASC");
while($u = mysqli_fetch_assoc($res_u)) {
    $usuarios_list[] = $u;
}

// 5. NUEVO: Obtener categorías de la base de datos
$categorias_list = [];
$res_c = mysqli_query($conexion, "SELECT * FROM categorias_stock ORDER BY nombre ASC");
while($c = mysqli_fetch_assoc($res_c)) {
    $categorias_list[] = $c;
}
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
            .then(reg => console.log('PWA lista en Gestión de Stock'))
            .catch(err => console.error('Error PWA Stock:', err));
        });
      }
    </script>
    <title>Stock - Cerrajería Pinos</title>
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/formularios.css">
    <style>
        /* --- ESTILOS MANTENIDOS --- */
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

        .btn-header:hover { background: rgba(255, 255, 255, 0.2); }
        .btn-cerrar-header { background: #e74c3c !important; border: 1px solid #c0392b !important; }

        @media (min-width: 768px) {
            .header-content { flex-direction: row; gap: 20px; }
            .nav-container { grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); }
        }

        .filter-container { display: flex; justify-content: center; gap: 10px; margin: 15px 0; flex-wrap: wrap; }
        .btn-filter { padding: 8px 15px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; color: white; transition: transform 0.2s; font-size: 0.9rem; }
        .btn-filter:active { transform: scale(0.95); }
        .grid-stock { display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 10px; }
        
        @media (max-width: 768px) {
            .btn-filter { flex: 1; min-width: 110px; }
        }

        /* Estilo para el mini-formulario de categorías */
        .admin-tools {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px dashed #2980b9;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <a href="dashboard.php">
            <img src="../img/logo.png" alt="Logo Cerrajeria Pinos" class="logo-img">
            </a>
            <h1>Stock</h1>
        </div>
        <nav class="nav-container">
            <a href="dashboard.php" class="btn-header">🏠 Panel</a>
            <a href="trabajos.php" class="btn-header">🛠️ Trabajos</a>
            <a href="plantillas.php" class="btn-header">🗒️ Plantillas</a>
            <?php if ($esAdmin): ?>
                <a href="ingresos.php" class="btn-header">💰 Ingresos</a>
                <a href="gastos.php" class="btn-header">💸 Gastos</a>
                <a href="gestion_usuarios.php" class="btn-header">👥 Empleados</a>
                <a href="impuestos.php" class="btn-header">📊 Impuestos</a>
                <a href="ingresosb.php" class="btn-header">🤫 Extras</a>
                <a href="empresas.php" class="btn-header">🏢 Empresas</a>
                <a href="proveedores.php" class="btn-header">🚚 Proveedores</a>
            <?php endif; ?>
            <a href="../php/logout.php" class="btn-header btn-cerrar-header">🚪 Salir</a>
        </nav>
    </header>

    <div class="search-container">
        <input type="text" id="buscadorStock" placeholder="Buscar por nombre de producto o categoría...">
    </div>

    <div class="filter-container">
        <button onclick="filtrarUbicacion('todos')" class="btn-filter" style="background: #7f8c8d;">🌐 Ver Todo</button>
        <button onclick="filtrarUbicacion('taller')" class="btn-filter" style="background: #2c3e50;">🏭 Taller</button>
        <?php foreach($usuarios_list as $u): ?>
            <button onclick="filtrarUbicacion(<?php echo $u['id']; ?>)" class="btn-filter" style="background: #2980b9;">🚐 <?php echo $u['nombre']; ?></button>
        <?php endforeach; ?>
    </div>

    <main class="stock-container">        
        <?php if ($esAdmin): ?>
        <section class="admin-tools">
            <h3 style="margin-top:0; color:#2980b9;">Gestionar Categorías</h3>
            
            <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 15px;">
                <input type="text" id="nuevaCategoriaNombre" placeholder="Nombre de nueva categoría..." class="input-style" style="flex: 1; margin-bottom:0;">
                <button onclick="guardarNuevaCategoria()" class="btn-guardar" style="margin-top:0; background:#27ae60;">+ Añadir</button>
            </div>

            <div style="background: #fff; padding: 10px; border-radius: 5px; border: 1px solid #ddd;">
                <label style="font-size: 0.8rem; color: #7f8c8d; display: block; margin-bottom: 5px;">Categorías actuales (Clic en el nombre para editar):</label>
                <div style="display: flex; flex-wrap: wrap; gap: 5px;">
                    <?php foreach($categorias_list as $cat): ?>
                        <span style="background: #ebf5fb; border: 1px solid #bdc3c7; padding: 2px 8px; border-radius: 15px; font-size: 0.85rem; display: flex; align-items: center; gap: 5px;">
                            <span onclick="editarCategoria(<?php echo $cat['id']; ?>, '<?php echo $cat['nombre']; ?>')" style="cursor: pointer; font-weight: 500;">
                                <?php echo $cat['nombre']; ?> ✏️
                            </span>
                            <button onclick="eliminarCategoria(<?php echo $cat['id']; ?>, '<?php echo $cat['nombre']; ?>')" style="border: none; background: none; color: #e74c3c; cursor: pointer; font-weight: bold; padding: 0 2px;">&times;</button>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="card-formulario">
            <h2>Añadir Nuevo Material</h2>
            <form id="formProducto" class="mi-formulario">
                <div class="input-group">
                    <label>Categoría</label>
                    <select name="categoria" id="selectCategoriaPrincipal" class="input-style" required>
                        <option value="">Seleccione una categoría...</option>
                        <?php foreach($categorias_list as $cat): ?>
                            <option value="<?php echo $cat['nombre']; ?>"><?php echo $cat['nombre']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="input-group">
                    <label for="nombre">Producto</label>
                    <input type="text" id="nombre" name="nombre" placeholder="Ej: Cerradura FAC" required>
                </div>
                <div class="input-group">
                    <label for="cantidad">Cantidad Initial (Almacén)</label>
                    <input type="number" id="cantidad" name="cantidad" placeholder="0" required>
                </div>
                <div class="input-group">
                    <label for="imagen">Imagen del Producto</label>
                    <input type="file" id="imagen" name="imagen" accept="image/*">
                </div>
                <button type="submit" class="btn-guardar">Guardar Stock</button>
            </form>
        </section>
        <?php endif; ?>

        <div class="table-card">
            <h2>Control de Existencias</h2>
            <table id="tablaStock">
                <thead>
                    <tr>
                        <th>Imagen</th>
                        <th>Producto</th>
                        <th>Categoría</th> 
                        <th>Taller</th>
                        <?php foreach($usuarios_list as $u): ?>
                            <th data-user-id="<?php echo $u['id']; ?>">F. <?php echo $u['nombre']; ?></th>
                        <?php endforeach; ?>
                        <th>Total</th>
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
        <div class="card-formulario modal-content" style="max-width: 600px;">
            <h2 style="color: #2980b9;">Editar Distribución de Stock</h2>
            <form id="formEditarStock" class="mi-formulario">
                <input type="hidden" name="id" id="edit_id">
                
                <div class="input-group">
                    <label>Nombre del Producto</label>
                    <input type="text" name="nombre" id="edit_nombre" required>
                </div>
                
                <div class="input-group">
                    <label>Categoría</label>
                    <select name="categoria" id="edit_categoria" class="input-style">
                        <?php foreach($categorias_list as $cat): ?>
                            <option value="<?php echo $cat['nombre']; ?>"><?php echo $cat['nombre']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <label style="font-weight: bold; margin-bottom: 10px; display: block;">Cantidades por ubicación:</label>
                <div id="contenedorCantidadesDinamicas" class="grid-stock">
                </div>

                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" class="btn-guardar" style="background: #2980b9; flex: 1;">Actualizar Todo</button>
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
            <p><span class="dot verde"></span> <strong>Suficiente:</strong> 10+ uds. totales.</p>
            <p><span class="dot naranja"></span> <strong>Bajo:</strong> 1-9 uds. totales.</p>
            <p><span class="dot rojo"></span> <strong>Agotado:</strong> 0 uds.</p>
            <hr>
            <p><strong>Filtros rápidos:</strong> Pulsa el icono de cada vehículo para ver qué materiales tiene cada operario.</p>
        </div>
    </aside>

    <script src="../js/stock.js"></script>
    <script>
        // No he añadido lógica aquí porque ya la tienes en el stock.js centralizado. 
        // Solo asegúrate de tener las funciones editarCategoria() y guardarNuevaCategoria() en tu archivo .js
    </script>
</body>
</html>