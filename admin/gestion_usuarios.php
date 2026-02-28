<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Verificamos si existe la sesión
if (!isset($_SESSION['usuario_id'])) {
    // CORRECCIÓN: Apuntamos al index.html en la raíz
    header("Location: ../index.html");
    exit();
}

// 2. Verificamos el rol (usando trim por seguridad de la BD)
$rol = isset($_SESSION['rol']) ? strtolower(trim($_SESSION['rol'])) : '';

if ($rol !== 'admin') {
    // Si es empleado, lo devolvemos al dashboard con un mensaje
    header("Location: dashboard.php?error=acceso_denegado");
    exit();
}

// Si llega aquí, es Admin y puede ver el contenido
include '../php/conexion.php'; 
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
    <title>Gestión de Personal - Cerrajería Pinos</title>
    <link rel="stylesheet" href="../css/header.css">
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/formularios.css">
    <link rel="stylesheet" href="../css/trabajos_layout.css">
    <link rel="stylesheet" href="../css/usuarios.css"> 
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
</style>
</head>
<body>
    <header>
        <div class="header-content">
            <a href="dashboard.php">
            <img src="../img/logo.png" alt="Logo Cerrajeria Pinos" class="logo-img">
            </a>
            <h1>Gestión de Personal</h1>
        </div>
        <input type="checkbox" id="menu-toggle">
        <label for="menu-toggle" class="hamburger">&#9776;</label>
        <nav class="nav-container">
            <a href="dashboard.php" class="btn-header">🏠 Panel</a>
            <a href="trabajos.php" class="btn-header">🛠️ Trabajos</a>
            <a href="gastos.php" class="btn-header">💸 Gastos</a>
            <a href="impuestos.php" class="btn-header">📊 Impuestos</a>
            <a href="ingresos.php" class="btn-header">💰 Ingresos</a>
            <a href="ingresosb.php" class="btn-header">🤫 Extras</a>
            <a href="stock.php" class="btn-header">📦 Stock</a>
            <a href="plantillas.php"class="btn-header">🗒️ Plantillas</a>
            <a href="empresas.php" class="btn-header"> 🏢 Empresas</a>
            <a href="proveedores.php" class="btn-header"> 🚚 Proveedores</a>
            <a href="clientes.php" class="btn-header">🗂️ Clientes</a>
            <a href="../php/logout.php" class="btn-header" style="background:#e74c3c;">Cerrar Sesión</a>
        </nav>
    </header>

    <div class="gestion-container">
        <div class="user-card-form">
            <h3>👤 Registrar Nuevo Usuario</h3>
            <p style="color: #666; font-size: 0.9em; margin-bottom: 20px;">Crea credenciales de acceso para tu equipo.</p>
            
            <form id="formNuevoUsuario" class="form-grid">
                <div class="input-group-user">
                    <label>Nombre Completo</label>
                    <input type="text" name="nombre" placeholder="Ej: Pedro Pinos" required>
                </div>
                <div class="input-group-user">
                    <label>Nombre de Usuario</label>
                    <input type="text" name="usuario" placeholder="Ej: ppinos" required>
                </div>
                <div class="input-group-user">
                    <label>Contraseña Acceso</label>
                    <input type="password" name="password" placeholder="••••••••" required>
                </div>
                <div class="input-group-user">
                    <label>Nivel de Permisos</label>
                    <select name="rol">
                        <option value="empleado">🛠️ Empleado (Limitado)</option>
                        <option value="admin">🔑 Administrador (Total)</option>
                    </select>
                </div>
                <button type="submit" class="btn-add-user">Dar de Alta</button>
            </form>
        </div>

        <div class="user-table-container">
            <h3 style="padding: 20px 20px 0 20px; margin: 0; color: #2c3e50;">Lista de Usuarios Activos</h3>
            <table class="user-table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Usuario</th>
                        <th>Rango / Permisos</th>
                        <th style="text-align: center;">Acciones</th>
                    </tr>
                </thead>
                <tbody id="listaUsuarios">
                    <?php
                    $res = mysqli_query($conexion, "SELECT id, nombre, usuario, rol FROM usuarios ORDER BY rol ASC, nombre ASC");
                    while($user = mysqli_fetch_assoc($res)): 
                        $esAutoconsulta = ($user['id'] == $_SESSION['usuario_id']);
                        $rolClass = ($user['rol'] === 'admin') ? 'badge-admin' : 'badge-empleado';
                        $rolIcon = ($user['rol'] === 'admin') ? '🔑' : '🛠️';
                    ?>
                    <tr>
                        <td data-label="Nombre" style="font-weight: 600;"><?php echo htmlspecialchars($user['nombre']); ?></td>
                        <td data-label="Usuario" style="color: #555;"><?php echo htmlspecialchars($user['usuario']); ?></td>
                        <td data-label="Permisos">
                            <span class="badge <?php echo $rolClass; ?>">
                                <?php echo $rolIcon . " " . strtoupper($user['rol']); ?>
                            </span>
                        </td>
                        <td data-label="Acciones" style="text-align: center;">
                            <?php if(!$esAutoconsulta): ?>
                                <button onclick="eliminarUsuario(<?php echo $user['id']; ?>)" class="btn-delete" title="Quitar acceso">
                                    🗑️ Eliminar
                                </button>
                            <?php else: ?>
                                <span style="font-size: 0.85em; color: #27ae60; font-weight: bold;">Estás conectado</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="../js/usuarios.js"></script>
</body>
</html>