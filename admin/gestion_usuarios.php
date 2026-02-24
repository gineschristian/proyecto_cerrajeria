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
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="theme-color" content="#2c3e50">
    <title>Gestión de Personal - Cerrajería Pinos</title>
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/formularios.css">
    <link rel="stylesheet" href="../css/trabajos_layout.css">
    <link rel="stylesheet" href="../css/usuarios.css"> 
    <style>
        /* Ajuste extra para que el menú con tantos enlaces no se rompa en móvil */
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
            <img src="../img/logo.png" alt="Logo Cerrajería Pinos" class="logo-img">
            <h1>Gestión de Personal</h1>
        </div>
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