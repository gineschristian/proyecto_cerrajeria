<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    // Si intenta entrar sin login, lo mandamos a la raíz donde está el index.html
    header("Location: ../index.html");
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
    <title>Panel de Control - Cerrajería Pinos</title>
    <link rel="stylesheet" href="../css/main.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <style>
        /* AJUSTES RESPONSIVE ESPECÍFICOS PARA EL DASHBOARD */
        .dashboard-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .card {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: transform 0.2s;
            height: 100%;
        }

        .card:active {
            transform: scale(0.98); /* Efecto de pulsación en móvil */
        }

        @media (max-width: 600px) {
            header {
                flex-direction: column;
                text-align: center;
                padding: 15px;
            }
            .header-content h1 {
                font-size: 1.2rem;
                margin-top: 10px;
            }
            .dashboard-container {
                grid-template-columns: 1fr; /* Una sola columna en móviles pequeños */
                padding: 10px;
            }
        }

        /* Estilos alertas */
        .alerta-stock {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            animation: slideIn 0.5s ease-out;
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <header style="display: flex; justify-content: space-between; align-items: center; padding: 10px 20px; background: #2c3e50; color: white;">
        <div class="header-content" style="display: flex; align-items: center; gap: 15px;">
            <img src="../img/logo.png" alt="Logo" class="logo-img" style="height: 50px;">
            <h1 style="margin: 0;">Hola David, <?php echo explode(' ', htmlspecialchars($_SESSION['nombre']))[0]; ?></h1>
        </div>
        <a href="../php/logout.php"><button style="background:#e74c3c; color: white; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer; font-weight: bold;">Cerrar Sesión</button></a>
    </header>

    <main class="dashboard-container">
        
        <div id="contenedorAlertas" style="grid-column: 1 / -1;">
            </div>

        <div class="card">
            <div class="card-icon" style="font-size: 2.5rem; margin-bottom: 10px;">🛠️</div>
            <h3>Trabajos</h3>
            <p>Registro de clientes y partes diarios.</p>
            <a href="trabajos.php" class="btn-acceder">Acceder</a>
        </div>

        <div class="card">
            <div class="card-icon" style="font-size: 2.5rem; margin-bottom: 10px;">📦</div>
            <h3>Stock</h3>
            <p>Inventario de material y llaves.</p>
            <a href="stock.php" class="btn-acceder">Acceder</a>
        </div>

        <div class="card">
            <div class="card-icon" style="font-size: 2.5rem; margin-bottom: 10px;">🗒️</div>
            <h3>Plantillas</h3>
            <p>Documentos y guías del taller.</p>
            <a href="plantillas.php" class="btn-acceder">Acceder</a>
        </div>

        <?php if ($esAdmin): ?>

            <div class="card" style="border-top: 5px solid #2ecc71;">
                <div class="card-icon" style="font-size: 2.5rem; margin-bottom: 10px;">💰</div>
                <h3>Ingresos</h3>
                <p>Control de facturación oficial (A).</p>
                <a href="ingresos.php" class="btn-acceder">Gestionar</a>
            </div>

            <div class="card" style="border-top: 5px solid #e74c3c;">
                <div class="card-icon" style="font-size: 2.5rem; margin-bottom: 10px;">💸</div>
                <h3>Gastos</h3>
                <p>Compras y pagos a proveedores.</p>
                <a href="gastos.php" class="btn-acceder">Gestionar</a>
            </div>

            <div class="card" style="border-top: 5px solid #f1c40f;">
                <div class="card-icon" style="font-size: 2.5rem; margin-bottom: 10px;">🤫</div>
                <h3>Extras</h3>
                <p>Caja secundaria y otros registros.</p>
                <a href="ingresosb.php" class="btn-acceder">Gestionar</a>
            </div>

            <div class="card" style="border-top: 5px solid #e67e22;">
                <div class="card-icon" style="font-size: 2.5rem; margin-bottom: 10px;">📊</div>
                <h3>Impuestos</h3>
                <p>Cierres trimestrales e IVA.</p>
                <a href="impuestos.php" class="btn-acceder">Gestionar</a>
            </div>

            <div class="card" style="border-top: 5px solid #8e44ad;">
                <div class="card-icon" style="font-size: 2.5rem; margin-bottom: 10px;">👥</div>
                <h3>Empleados</h3>
                <p>Alta y baja de usuarios.</p>
                <a href="gestion_usuarios.php" class="btn-acceder">Gestionar</a>
            </div>

            <div class="card" style="border-top: 5px solid #34495e;">
                <div class="card-icon" style="font-size: 2.5rem; margin-bottom: 10px;">🛡️</div>
                <h3>Seguridad</h3>
                <p>Copia de seguridad del sistema.</p>
                <a href="../php/backup.php" class="btn-acceder" style="background: #34495e; color: white;">
                    📥 Backup .SQL
                </a>
            </div>

        <?php endif; ?>

    </main>
    <script src="../js/dashboard.js"></script>
</body>
</html>